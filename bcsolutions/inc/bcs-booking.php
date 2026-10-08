<?php
// =============================================================================
//  BCS Booking & Amelia Integration
//  Dipecah dari functions.php untuk maintainability
// =============================================================================

// =============================================================================
//  AMELIA BOOKING → GOOGLE SHEETS (via Google Apps Script Webhook)
//  -------------------------------------------------------------------------
//  Hooks into Amelia's internal WordPress actions to send booking data
//  to Google Sheets automatically — no Amelia Webhooks feature needed.
//
//  v2 (11 Mei 2026): defensive multi-path lookup, extras support, raw debug
// =============================================================================

// PHASE 1 — TEST: pointing to spreadsheet milik sendiri (untuk verifikasi data masuk dengan benar)
// PHASE 2 — LIVE: ganti URL ke deployment baru yang connected ke spreadsheet Kak Bella
define('BCS_GOOGLE_SHEETS_WEBHOOK_URL', 'https://script.google.com/macros/s/AKfycbyW-2sbbV2-fWIV39NbaKvzUKgqVOdoDXYhuTLTcZbk6xh755BYMIQ-zwU7cSmdzUkZKg/exec');
define('BCS_SHEETS_DEBUG', true); // set false di production

/**
 * Amelia: izinkan client booking dengan email yang sama meski namanya berbeda.
 * Menghapus validasi "Email already exists with different name".
 */
add_filter('amelia_customer_booking_data', function ($data) {
    if (isset($data['existingCustomer'])) {
        // Update nama customer di DB agar cocok dengan input baru
        unset($data['existingCustomer']['differentName']);
    }
    return $data;
});

// Supres error "Email already exists with different name" di response Amelia
add_filter('amelia_booking_response', function ($response) {
    if (
        isset($response['data']['message']) &&
        strpos($response['data']['message'], 'different name') !== false
    ) {
        unset($response['data']['message']);
    }
    return $response;
});

/**
 * Helper: cari nilai di array nested dengan multiple candidate paths.
 * Path format: "a.b.c" — pakai dot notation
 */
function bcs_array_get_path($arr, $path, $default = '')
{
    if (empty($arr)) {
        return $default;
    }
    $keys = explode('.', $path);
    $cur = $arr;
    foreach ($keys as $k) {
        if (is_object($cur)) {
            $cur = (array) $cur;
        }
        if (!is_array($cur) || !isset($cur[$k])) {
            return $default;
        }
        $cur = $cur[$k];
    }
    return $cur !== null ? $cur : $default;
}

/**
 * Helper: coba beberapa path, return value pertama yang ketemu (non-empty)
 */
function bcs_first_value($arr, $paths, $default = '')
{
    foreach ((array) $paths as $path) {
        $v = bcs_array_get_path($arr, $path, null);
        if ($v !== null && $v !== '') {
            return $v;
        }
    }
    return $default;
}

/**
 * Normalize Amelia reservation array → flat payload for Apps Script
 */
function bcs_normalize_amelia_reservation($reservation)
{
    // Cast top-level to array if object
    if (is_object($reservation)) {
        $reservation = (array) $reservation;
    }

    // === Customer ===
    // Amelia menyimpan customer di banyak tempat tergantung versi/event:
    //   - $reservation['customer']
    //   - $reservation['booking']['customer']
    //   - $reservation['bookings'][0]['customer']
    //   - $reservation['appointment']['bookings'][0]['customer']
    $first_name = bcs_first_value($reservation, [
        'customer.firstName',
        'booking.customer.firstName',
        'bookings.0.customer.firstName',
        'appointment.bookings.0.customer.firstName',
    ]);
    $last_name = bcs_first_value($reservation, [
        'customer.lastName',
        'booking.customer.lastName',
        'bookings.0.customer.lastName',
        'appointment.bookings.0.customer.lastName',
    ]);
    $email = bcs_first_value($reservation, [
        'customer.email',
        'booking.customer.email',
        'bookings.0.customer.email',
        'appointment.bookings.0.customer.email',
    ]);
    $phone = bcs_first_value($reservation, [
        'customer.phone',
        'booking.customer.phone',
        'bookings.0.customer.phone',
        'appointment.bookings.0.customer.phone',
    ]);

    // === Service ===
    $service_name = bcs_first_value($reservation, [
        'service.name',
        'appointment.service.name',
        'serviceName',
    ]);
    $service_id = bcs_first_value($reservation, [
        'service.id',
        'appointment.service.id',
        'appointment.serviceId',
        'serviceId',
    ], 0);

    // === Category ===
    $category_name = bcs_first_value($reservation, [
        'category.name',
        'service.category.name',
        'appointment.service.category.name',
    ]);

    // Fallback: kalau service ID ada tapi nama kosong, lookup ke DB Amelia
    if (empty($service_name) && !empty($service_id)) {
        $service_data = bcs_lookup_amelia_service($service_id);
        if ($service_data) {
            $service_name = $service_name ?: $service_data['name'];
            $category_name = $category_name ?: $service_data['category_name'];
        }
    }

    // === Datetime, price, status ===
    $booking_start = bcs_first_value($reservation, [
        'booking.bookingStart',
        'appointment.bookingStart',
        'bookings.0.bookingStart',
        'bookingStart',
    ]);
    $booking_end = bcs_first_value($reservation, [
        'booking.bookingEnd',
        'appointment.bookingEnd',
        'bookings.0.bookingEnd',
        'bookingEnd',
    ]);
    $price = bcs_first_value($reservation, [
        'booking.price',
        'appointment.price',
        'bookings.0.price',
        'price',
    ], 0);
    $status = bcs_first_value($reservation, [
        'booking.status',
        'appointment.status',
        'bookings.0.status',
        'status',
    ], 'pending');

    // === Extras (add-ons) ===
    // Amelia menyimpan extras di:
    //   - $reservation['booking']['extras']
    //   - $reservation['bookings'][0]['extras']
    //   - $reservation['appointment']['bookings'][0]['extras']
    $extras_raw = bcs_first_value($reservation, [
        'booking.extras',
        'bookings.0.extras',
        'appointment.bookings.0.extras',
    ], array());

    $extras_list = array();
    if (is_array($extras_raw)) {
        foreach ($extras_raw as $extra) {
            if (is_object($extra)) {
                $extra = (array) $extra;
            }
            $extra_name = bcs_first_value($extra, ['name', 'extra.name'], '');
            $extra_qty = isset($extra['quantity']) ? intval($extra['quantity']) : 1;
            $extra_price = isset($extra['price']) ? $extra['price'] : 0;

            // Fallback: lookup name by extraId
            $extra_id = isset($extra['extraId']) ? $extra['extraId'] : (isset($extra['id']) ? $extra['id'] : 0);
            if (empty($extra_name) && !empty($extra_id)) {
                $extra_name = bcs_lookup_amelia_extra_name($extra_id);
            }

            if (!empty($extra_name)) {
                $extras_list[] = $extra_name . ' x' . $extra_qty;
            }
        }
    }
    $extras_str = implode('; ', $extras_list);

    return array(
        'appointment' => array(
            'bookingStart' => $booking_start,
            'bookingEnd' => $booking_end,
            'price' => $price,
            'status' => $status,
            'customer' => array(
                'firstName' => $first_name,
                'lastName' => $last_name,
                'email' => $email,
                'phone' => $phone,
            ),
            'service' => array(
                'name' => $service_name,
                'category' => array('name' => $category_name),
            ),
            'extras' => $extras_str,
        ),
    );
}

/**
 * Lookup service info from Amelia DB by service ID
 * Returns ['name', 'category_name'] or null
 */
function bcs_lookup_amelia_service($service_id)
{
    global $wpdb;
    $service_id = intval($service_id);
    if (!$service_id)
        return null;

    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT s.name AS service_name, c.name AS category_name
         FROM {$wpdb->prefix}amelia_services s
         LEFT JOIN {$wpdb->prefix}amelia_categories c ON c.id = s.categoryId
         WHERE s.id = %d",
        $service_id
    ), ARRAY_A);

    if (!$row)
        return null;
    return array(
        'name' => $row['service_name'],
        'category_name' => $row['category_name'],
    );
}

/**
 * Lookup extra name from Amelia DB by extra ID
 */
function bcs_lookup_amelia_extra_name($extra_id)
{
    global $wpdb;
    $extra_id = intval($extra_id);
    if (!$extra_id)
        return '';

    $name = $wpdb->get_var($wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}amelia_services_extras WHERE id = %d",
        $extra_id
    ));
    return $name ?: '';
}

/**
 * Helper: extract YITH Product Add-On labels dari satu WC cart item.
 * Defensive: try multiple key paths karena struktur YITH bervariasi per versi.
 *
 * Return: array of unique label strings (e.g. ['Additional Overtime – 30 Minutes']).
 */
function bcs_extract_addon_labels_from_cart_item($cart_item)
{
    $labels = array();

    // Cek banyak kemungkinan key YITH (struktur berbeda per versi)
    $key_candidates = array(
        'ywapo_meta_data',
        '_ywapo_meta_data',
        'yith_wapo_meta_data',
        '_yith_wapo_meta_data',
        'yith_wapo',
        '_yith_wapo',
        'yith_wapo_options',
        '_ywapo_addon_data',
        'addons',
        '_addons',
    );

    $found_groups = array();
    foreach ($key_candidates as $key) {
        if (!empty($cart_item[$key]) && is_array($cart_item[$key])) {
            $found_groups[] = array('key' => $key, 'data' => $cart_item[$key]);
        }
    }

    // Diagnostic: kalau debug ON & ga ada match, dump semua keys yg ada di cart_item
    // supaya bisa identify YITH meta key actual
    if (empty($found_groups) && defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG) {
        $cart_keys = array_keys($cart_item);
        $custom_keys = array_filter($cart_keys, function ($k) {
            // Skip standard WC keys
            return !in_array($k, array(
                'key',
                'product_id',
                'variation_id',
                'variation',
                'quantity',
                'data',
                'data_hash',
                'line_tax_data',
                'line_subtotal',
                'line_subtotal_tax',
                'line_total',
                'line_tax',
            ), true);
        });
        if (!empty($custom_keys)) {
            error_log('[BCS-AddonExtract] No YITH match. Custom cart_item keys: ' . implode(',', $custom_keys));
            // Dump first 500 chars per custom key
            foreach ($custom_keys as $ck) {
                $dump = is_array($cart_item[$ck]) ? wp_json_encode($cart_item[$ck]) : (string) $cart_item[$ck];
                error_log("[BCS-AddonExtract]   {$ck} = " . substr($dump, 0, 500));
            }
        }
    }

    // Iterate 2 levels deep. Support BOTH structures:
    //   A) Newer YITH (yith_wapo_options): [ {addon_id => "Label string"} ]
    //   B) Older YITH (ywapo_meta_data):   [ addon_id => [option_id => {label, price, ...}] ]
    foreach ($found_groups as $entry) {
        $group_list = $entry['data'];
        foreach ($group_list as $group) {
            if (!is_array($group))
                continue;
            foreach ($group as $opt_key => $opt) {
                // Structure A: $opt is a STRING (the label itself)
                if (is_string($opt)) {
                    $opt = trim($opt);
                    if ($opt !== '') {
                        $labels[] = $opt;
                    }
                    continue;
                }
                // Structure B: $opt is an ARRAY with label/name/option_label key
                if (!is_array($opt))
                    continue;
                $label = '';
                if (isset($opt['label'])) {
                    $label = $opt['label'];
                } elseif (isset($opt['name'])) {
                    $label = $opt['name'];
                } elseif (isset($opt['option_label'])) {
                    $label = $opt['option_label'];
                }
                if (!empty($label) && is_string($label)) {
                    $labels[] = $label;
                }
            }
        }
    }

    return array_values(array_unique($labels));
}

/**
 * Helper: read current WC cart untuk extract YITH add-ons + total price.
 * Dipanggil saat Amelia hook fire untuk augment payload (1 row per booking).
 *
 * @param int $filter_service_id Kalau >0, hanya hitung cart item yang
 *                               mapped ke service_id ini (via _amelia_service_id meta).
 *                               Penting supaya harga di sheet = per-booking, bukan total cart.
 *
 * Return: ['extras' => 'Add-on A; Add-on B', 'total' => 300000]
 * Kalau cart kosong / WC tidak active, return empty default.
 */
function bcs_get_current_cart_extras_and_total($filter_service_id = 0)
{
    $result = array('extras' => '', 'total' => 0.0);

    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
        return $result;
    }

    $filter_service_id = intval($filter_service_id);
    $extras_list = array();

    foreach (WC()->cart->get_cart() as $cart_item) {
        // Filter by service_id kalau diberikan
        if ($filter_service_id > 0) {
            $product_id = isset($cart_item['product_id']) ? intval($cart_item['product_id']) : 0;
            if (!$product_id)
                continue;
            $mapped_id = intval(get_post_meta($product_id, '_amelia_service_id', true));
            if ($mapped_id !== $filter_service_id)
                continue;
        }

        // Akumulasi total (line_total sudah include YITH add-on price)
        $line_total = isset($cart_item['line_total']) ? floatval($cart_item['line_total']) : 0;
        $line_tax = isset($cart_item['line_tax']) ? floatval($cart_item['line_tax']) : 0;
        $result['total'] += $line_total + $line_tax;

        // Extract YITH add-on labels (shared helper)
        $labels = bcs_extract_addon_labels_from_cart_item($cart_item);
        foreach ($labels as $l) {
            $extras_list[] = $l;
        }
    }

    // Dedupe + assemble
    $extras_list = array_values(array_unique($extras_list));
    $result['extras'] = implode('; ', $extras_list);

    return $result;
}

/**
 * =============================================================================
 *  DYNAMIC AMELIA DURATION OVERRIDE (berdasarkan YITH add-ons di cart)
 *  -------------------------------------------------------------------------
 *  Mapping label add-on → durasi (menit). Edit di sini kalau ada add-on baru.
 *  Filter: 'bcs_addon_duration_map' (kalau mau modify dari plugin/snippet lain).
 *
 *  PENTING: label HARUS persis sama dengan YITH (case + tanda baca).
 *  En-dash "–" (U+2013) BUKAN hyphen biasa "-". Copy-paste dari YITH settings.
 * =============================================================================
 */
function bcs_addon_duration_minutes_map()
{
    // PENTING: label HARUS persis sama dengan YITH output (case + en-dash + spasi).
    // Confirmed dari debug.log: YITH simpan label langsung sebagai value string.
    $map = array(
        'Additional Overtime – 30 Minutes' => 30,
        'Additional Make Up Room / Preparation – 60 Minutes' => 60,
    );
    return apply_filters('bcs_addon_duration_map', $map);
}

/**
 * Mapping label add-on → harga (IDR). Dipakai untuk override Amelia service price
 * supaya widget di /booking/ juga menampilkan total harga yang benar.
 *
 * PENTING: label harus match dengan `bcs_addon_duration_minutes_map()`.
 */
function bcs_addon_price_map()
{
    $map = array(
        'Additional Overtime – 30 Minutes' => 150000,
        'Additional Make Up Room / Preparation – 60 Minutes' => 400000,
    );
    return apply_filters('bcs_addon_price_map', $map);
}

/**
 * Hitung total extra price dari cart item yang match service_id.
 * Digunakan untuk override Amelia price display di widget.
 */
function bcs_get_cart_extra_price_for_service($service_id)
{
    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
        return 0;
    }

    $service_id = intval($service_id);
    if ($service_id <= 0)
        return 0;

    $map = bcs_addon_price_map();
    $extra_price = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = isset($cart_item['product_id']) ? intval($cart_item['product_id']) : 0;
        if (!$product_id)
            continue;

        $mapped_id = intval(get_post_meta($product_id, '_amelia_service_id', true));
        if ($mapped_id !== $service_id)
            continue;

        $labels = bcs_extract_addon_labels_from_cart_item($cart_item);
        foreach ($labels as $label) {
            if (isset($map[$label])) {
                $extra_price += floatval($map[$label]);
            }
        }
    }

    return $extra_price;
}

/**
 * Hitung total extra duration (detik) dari cart item yang match service_id.
 * Hanya hitung add-on yang ada di mapping (unknown labels diabaikan).
 */
function bcs_get_cart_extra_duration_for_service($service_id)
{
    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
        return 0;
    }

    $service_id = intval($service_id);
    if ($service_id <= 0)
        return 0;

    $debug = defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG;
    $map = bcs_addon_duration_minutes_map();
    $extra_seconds = 0;

    // Debug: dump full cart structure ONCE per request (controlled via static flag)
    static $cart_dumped = false;
    if ($debug && !$cart_dumped) {
        $cart_dumped = true;
        foreach (WC()->cart->get_cart() as $cart_key => $item) {
            $std = array('key', 'product_id', 'variation_id', 'variation', 'quantity', 'data', 'data_hash', 'line_tax_data', 'line_subtotal', 'line_subtotal_tax', 'line_total', 'line_tax');
            $custom_keys = array_diff(array_keys($item), $std);
            $pid = isset($item['product_id']) ? $item['product_id'] : '?';
            $mapped = intval(get_post_meta($pid, '_amelia_service_id', true));
            error_log("[BCS-CartDump] product_id={$pid} _amelia_service_id={$mapped} custom_keys=" . implode(',', $custom_keys));
            foreach ($custom_keys as $ck) {
                $val = $item[$ck];
                $dump = is_array($val) ? wp_json_encode($val) : (string) $val;
                error_log("[BCS-CartDump]   {$ck} = " . substr($dump, 0, 800));
            }
        }
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = isset($cart_item['product_id']) ? intval($cart_item['product_id']) : 0;
        if (!$product_id)
            continue;

        $mapped_id = intval(get_post_meta($product_id, '_amelia_service_id', true));
        if ($debug) {
            error_log("[BCS-DurCheck] product={$product_id} mapped_amelia_id={$mapped_id} target={$service_id} match=" . ($mapped_id === $service_id ? 'YES' : 'NO'));
        }
        if ($mapped_id !== $service_id)
            continue; // hanya cart item yg match service

        $labels = bcs_extract_addon_labels_from_cart_item($cart_item);
        if ($debug) {
            error_log("[BCS-DurCheck] product {$product_id} extracted labels: " . wp_json_encode($labels));
        }
        foreach ($labels as $label) {
            if (isset($map[$label])) {
                $extra_seconds += intval($map[$label]) * 60;
            } elseif ($debug) {
                error_log("[BCS-DurCheck] Label '{$label}' NOT in duration map");
            }
        }
    }

    return $extra_seconds;
}

/**
 * Intercept Amelia REST requests → override service.duration di DB berdasarkan
 * extra duration dari YITH cart. Restore setelah request via shutdown hook.
 *
 * Cara kerja:
 *   1. Detect request ke /wp-json/amelia/v1/* dengan serviceId param
 *   2. Hitung extra duration dari cart (kalau ada)
 *   3. UPDATE wp_amelia_services SET duration = base + extra
 *   4. Register shutdown function untuk RESTORE duration ke base value
 *
 * Side effects:
 *   - DB write per Amelia request (low overhead, indexed by id)
 *   - Race condition possible jika 2 user request bersamaan dgn cart berbeda
 *     (acceptable untuk traffic studio kecil)
 */
/**
 * Detect serviceId dari current request (REST/AJAX/regular POST/GET).
 * Cek $_REQUEST + JSON body + Referer URL.
 */
function bcs_detect_service_id_from_request()
{
    // Direct from $_REQUEST (covers GET + POST form data + AJAX payload)
    foreach (array('serviceId', 'service_id', 'service') as $key) {
        if (!empty($_REQUEST[$key])) {
            $sid = intval($_REQUEST[$key]);
            if ($sid > 0)
                return $sid;
        }
    }

    // From raw JSON body (axios/fetch POST)
    $raw = @file_get_contents('php://input');
    if (!empty($raw)) {
        $body = @json_decode($raw, true);
        if (is_array($body)) {
            foreach (array('serviceId', 'service_id', 'service') as $key) {
                if (!empty($body[$key])) {
                    $sid = intval($body[$key]);
                    if ($sid > 0)
                        return $sid;
                }
            }
        }
    }

    // From HTTP_REFERER (e.g. /booking/?service=6) — fallback kalau Amelia AJAX
    // tidak pass serviceId secara explicit di payload availability check
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $parts = parse_url($_SERVER['HTTP_REFERER']);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $qs);
            foreach (array('service', 'serviceId', 'service_id') as $key) {
                if (!empty($qs[$key])) {
                    $sid = intval($qs[$key]);
                    if ($sid > 0)
                        return $sid;
                }
            }
        }
    }

    return 0;
}

/**
 * Cek apakah request saat ini terkait Amelia / booking flow.
 * Return true kalau:
 *   - $_REQUEST['action'] mengandung 'amelia'
 *   - $_REQUEST['command'] mengandung 'amelia'
 *   - REQUEST_URI mengandung 'amelia' atau 'booking'
 *
 * Dipakai sebagai request gate di awal bcs_amelia_apply_duration_override()
 * untuk skip request tidak relevan (heartbeat, elementor_ajax, autosave, dll)
 * sebelum load WC cart yang boros memory.
 */
function bcs_is_amelia_context()
{
    // 1. Check admin-ajax action name
    $action = isset($_REQUEST['action']) ? strtolower((string) $_REQUEST['action']) : '';
    if ($action !== '' && stripos($action, 'amelia') !== false)
        return true;

    // 2. Check Amelia-specific 'command' param
    $command = isset($_REQUEST['command']) ? strtolower((string) $_REQUEST['command']) : '';
    if ($command !== '' && stripos($command, 'amelia') !== false)
        return true;

    // 3. Check REQUEST_URI (REST, shortcode page, custom endpoint)
    $uri = isset($_SERVER['REQUEST_URI']) ? strtolower((string) $_SERVER['REQUEST_URI']) : '';
    if ($uri !== '' && (stripos($uri, 'amelia') !== false || stripos($uri, 'booking') !== false))
        return true;

    return false;
}

/**
 * Apply duration + price override untuk Amelia request saat ini.
 * Dipanggil dari multiple hooks (REST + AJAX + init + template_redirect) untuk catch-all.
 * Idempotent: pakai static flag supaya tidak override ganda di 1 request.
 *
 * Override BOTH:
 *   - wp_amelia_services.duration (detik)  — supaya slot Amelia auto-extend
 *   - wp_amelia_services.price             — supaya Summary Amelia tampilkan harga total
 *
 * Restore ke base value via register_shutdown_function.
 */
function bcs_amelia_apply_duration_override()
{
    static $already_applied = false;
    if ($already_applied)
        return;

    // Request gate — skip entirely kalau request BUKAN Amelia-related.
    // Mencegah wc_load_cart() di-panggil pada heartbeat, elementor_ajax, autosave, dll.
    // yang menyebabkan memory overhead besar (PHP 128M tidak cukup untuk Elementor save
    // saat WC cart juga di-load).
    if (!bcs_is_amelia_context())
        return;

    $debug = defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG;

    // Init WC cart (WC tidak auto-load di REST/AJAX context)
    if (function_exists('wc_load_cart')) {
        wc_load_cart();
    }

    $service_id = bcs_detect_service_id_from_request();

    if ($debug) {
        $cart_count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 'no-cart';
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '(none)';
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        error_log("[BCS-AmeliaDur] apply_override action={$action} uri={$uri} serviceId={$service_id} cart_items={$cart_count}");
    }

    if (!$service_id)
        return;

    $extra_seconds = bcs_get_cart_extra_duration_for_service($service_id);
    $extra_price = bcs_get_cart_extra_price_for_service($service_id);

    if ($debug) {
        error_log("[BCS-AmeliaDur] service {$service_id} extra_seconds={$extra_seconds} extra_price={$extra_price}");
    }

    if ($extra_seconds <= 0 && $extra_price <= 0)
        return;

    global $wpdb;
    $table = $wpdb->prefix . 'amelia_services';

    // CRITICAL: gunakan stored "true original" dari wp_options, BUKAN current DB value.
    // Kalau pakai current DB, request concurrent bisa baca nilai yang sudah ter-override
    // (treated as base) → stacking bug: 3600→5400→7200→9000...
    //
    // Stored originals di-init SEKALI saat first sight (kalau wp_options key belum ada,
    // baca current DB sebagai source of truth). Setelah itu, originals diabsen di sini.
    $opt_key = "bcs_amelia_orig_svc_{$service_id}";
    $orig = get_option($opt_key, null);
    if (!is_array($orig) || !isset($orig['duration']) || !isset($orig['price'])) {
        $row = $wpdb->get_row($wpdb->prepare("SELECT duration, price FROM {$table} WHERE id = %d", $service_id), ARRAY_A);
        if (empty($row))
            return;
        $orig = array(
            'duration' => intval($row['duration']),
            'price' => floatval($row['price']),
        );
        update_option($opt_key, $orig, true); // autoload=true (cepat)
        if ($debug) {
            error_log("[BCS-AmeliaDur] INIT originals service {$service_id} dur={$orig['duration']} price={$orig['price']} (saved to wp_options)");
        }
    }

    $original_duration = intval($orig['duration']);
    $original_price = floatval($orig['price']);

    // Idempotent: nilai target SELALU sama (orig + extras), tidak peduli current DB state.
    $new_duration = $original_duration + $extra_seconds;
    $new_price = $original_price + $extra_price;

    if ($debug) {
        error_log("[BCS-AmeliaDur] OVERRIDE service {$service_id} dur:{$original_duration}→{$new_duration} price:{$original_price}→{$new_price}");
    }

    $wpdb->update(
        $table,
        array('duration' => $new_duration, 'price' => $new_price),
        array('id' => $service_id)
    );
    $already_applied = true;

    // Restore via shutdown — gunakan stored originals (BUKAN local var dari current DB read).
    // Multiple concurrent shutdowns akan restore ke nilai yang sama → tidak ada stale value.
    register_shutdown_function(function () use ($table, $service_id, $original_duration, $original_price) {
        global $wpdb;
        $wpdb->update(
            $table,
            array('duration' => $original_duration, 'price' => $original_price),
            array('id' => $service_id)
        );
    });
}

/**
 * Hook 1: REST API (untuk Amelia Pro / new versions yg pakai /wp-json/.../amelia/...)
 */
function bcs_amelia_dynamic_duration_pre($result, $server, $request)
{
    $route = $request->get_route();
    if (stripos($route, 'amelia') === false)
        return $result;

    if (defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG) {
        error_log("[BCS-AmeliaDur] REST route hit: {$route}");
    }
    bcs_amelia_apply_duration_override();
    return $result;
}
add_filter('rest_pre_dispatch', 'bcs_amelia_dynamic_duration_pre', 10, 3);

/**
 * Hook 2: admin-ajax.php (Amelia Lite + most installations).
 * Hook ke beberapa kemungkinan action name (dengan priority 1, sebelum Amelia process).
 */
function bcs_amelia_dynamic_duration_ajax()
{
    bcs_amelia_apply_duration_override();
}
foreach (array('wpamelia_api', 'amelia_api', 'amelia', 'wpamelia') as $bcs_amelia_action) {
    add_action("wp_ajax_{$bcs_amelia_action}", 'bcs_amelia_dynamic_duration_ajax', 1);
    add_action("wp_ajax_nopriv_{$bcs_amelia_action}", 'bcs_amelia_dynamic_duration_ajax', 1);
}
unset($bcs_amelia_action);

/**
 * Hook 2b: Universal admin-ajax catch-all.
 * Karena kita tidak tahu PERSIS action name Amelia, kita juga hook di `admin_init`
 * yang fire untuk SEMUA ajax request. Override function punya static flag + early
 * returns, jadi aman di-spam.
 */
function bcs_amelia_dynamic_duration_admin_init()
{
    if (!defined('DOING_AJAX') || !DOING_AJAX)
        return;
    // Skip early kalau bukan Amelia-context — hindari load WC cart di heartbeat/elementor_ajax/dll.
    if (!bcs_is_amelia_context())
        return;
    bcs_amelia_apply_duration_override();
}
add_action('admin_init', 'bcs_amelia_dynamic_duration_admin_init', 1);

/**
 * Hook 4: Page load (initial render of /booking/?service=X).
 * Amelia shortcode render service HTML (termasuk duration "1 Hour") server-side
 * pakai data dari wp_amelia_services. Jadi override harus aktif SEBELUM
 * `the_content` filter fire.
 */
function bcs_amelia_dynamic_duration_template()
{
    // Hanya jalan untuk frontend page (skip admin, feeds, cron, dll.)
    if (is_admin() || (defined('DOING_CRON') && DOING_CRON))
        return;
    bcs_amelia_apply_duration_override();
}
add_action('template_redirect', 'bcs_amelia_dynamic_duration_template', 1);

/**
 * Hook 5: Late init catch-all — tangkap request yang tidak lewat REST/AJAX/template.
 * E.g. Amelia kadang pakai custom URL rewrite atau endpoint aneh.
 */
function bcs_amelia_dynamic_duration_wp_loaded()
{
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    // Only fire on Amelia-related URIs (defensive — don't slow down unrelated requests)
    if (stripos($uri, 'amelia') === false && stripos($uri, 'booking') === false)
        return;
    bcs_amelia_apply_duration_override();
}
add_action('wp_loaded', 'bcs_amelia_dynamic_duration_wp_loaded', 5);

/**
 * Hook 3: Diagnostic — log ALL admin-ajax requests when DEBUG=true.
 * Tujuan: cari tahu PERSIS action name yang Amelia pakai, supaya bisa adjust hook.
 * Aman dimatikan setelah confirmed (set BCS_SHEETS_DEBUG=false).
 */
function bcs_amelia_diag_log_ajax()
{
    if (!defined('BCS_SHEETS_DEBUG') || !BCS_SHEETS_DEBUG)
        return;
    if (!defined('DOING_AJAX') || !DOING_AJAX)
        return;

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '(none)';
    $command = isset($_REQUEST['command']) ? $_REQUEST['command'] : '';
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

    // Only log if remotely Amelia-related (action/command/URI contains 'amelia' or 'booking')
    $haystack = strtolower($action . '|' . $command . '|' . $uri);
    if (stripos($haystack, 'amelia') === false && stripos($haystack, 'booking') === false) {
        return;
    }

    error_log("[BCS-Diag] AJAX action={$action} command={$command} URI={$uri}");
}
add_action('init', 'bcs_amelia_diag_log_ajax', 1);

/**
 * =============================================================================
 *  CART CLEAR BEFORE ADD AMELIA PRODUCT
 *  -------------------------------------------------------------------------
 *  Setiap kali customer klik "Check Availability" (yang sebenarnya adalah
 *  WC add-to-cart untuk product dengan _amelia_service_id), kita CLEAR cart
 *  dulu supaya hanya ada 1 booking item.
 *
 *  Tujuan:
 *  - Cart tidak menumpuk item lama kalau customer add ulang product berbeda
 *  - Harga di checkout = persis harga booking saat ini, bukan accumulate
 *  - Konsisten dgn flow 1 booking = 1 transaksi
 * =============================================================================
 */
function bcs_clear_cart_before_amelia_add($cart_item_data, $product_id)
{
    $amelia_id = get_post_meta($product_id, '_amelia_service_id', true);
    if (!$amelia_id) {
        return $cart_item_data; // non-Amelia product → biarkan apa adanya
    }

    if (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
        WC()->cart->empty_cart();
        if (defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG) {
            error_log("[BCS-Cart] Cleared cart before adding Amelia product {$product_id}");
        }
    }

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'bcs_clear_cart_before_amelia_add', 10, 2);

/**
 * Send Amelia booking data to Google Sheets via Apps Script
 *
 * Strategi: augment payload Amelia dengan data dari WC cart
 * (add-ons + total final) supaya 1 row di sheet sudah lengkap.
 */
function bcs_amelia_send_to_sheets($reservation)
{
    $webhook_url = BCS_GOOGLE_SHEETS_WEBHOOK_URL;
    if (empty($webhook_url)) {
        return;
    }

    // Pastikan WC cart loaded — Amelia hook bisa fire di REST/AJAX context
    // di mana WC cart tidak auto-init.
    if (function_exists('wc_load_cart')) {
        wc_load_cart();
    }

    // Build normalized payload dari Amelia reservation
    $payload = bcs_normalize_amelia_reservation($reservation);

    // Extract service_id dari reservation untuk filter cart per booking
    $service_id = intval(bcs_first_value($reservation, [
        'service.id',
        'appointment.service.id',
        'appointment.serviceId',
        'serviceId',
    ], 0));

    // Augment dengan WC cart data — filter by service_id supaya harga = per booking,
    // bukan total seluruh cart (kalau customer punya multiple items di cart)
    $cart_data = bcs_get_current_cart_extras_and_total($service_id);
    if (!empty($cart_data['extras'])) {
        $payload['appointment']['extras'] = $cart_data['extras'];
    }
    if ($cart_data['total'] > 0) {
        // Override price dengan total line item (base + add-ons untuk service ini)
        $payload['appointment']['price'] = $cart_data['total'];
    }

    // Debug: log raw + normalized + cart
    if (BCS_SHEETS_DEBUG) {
        error_log('[BCS-Sheets] RAW reservation: ' . wp_json_encode($reservation));
        error_log('[BCS-Sheets] CART data: ' . wp_json_encode($cart_data));
        error_log('[BCS-Sheets] FINAL payload: ' . wp_json_encode($payload));
    }

    // Send to Apps Script (non-blocking fire-and-forget)
    wp_remote_post($webhook_url, array(
        'method' => 'POST',
        'timeout' => 15,
        'blocking' => false,
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode($payload),
    ));
}

// Hook into Amelia events — HANYA initial booking + cancel/reschedule
// StatusUpdated DIHAPUS karena menyebabkan data double (fire bersamaan dengan BookingAdded)
add_action('AmeliaBookingAddedBeforeNotify', 'bcs_amelia_send_to_sheets');
add_action('AmeliaBookingCanceledBeforeNotify', 'bcs_amelia_send_to_sheets');
add_action('AmeliaBookingRescheduledBeforeNotify', 'bcs_amelia_send_to_sheets');

// =============================================================================
//  AMELIA + WOOCOMMERCE BOOKING FLOW (YITH-only add-ons, v2)
//  -------------------------------------------------------------------------
//  Flow:
//  1. /bc-creative-studio/ (shop) → klik "Book Now" → /product/X/ (detail)
//  2. /product/X/ → pilih add-ons di YITH → klik "Check Availability"
//     → product (with YITH add-ons) ditambahkan ke cart
//     → redirect ke /booking/?service=X
//  3. /booking/ → Amelia: pick date/time → submit booking
//     → JS auto-redirect ke /checkout/
//  4. /checkout/ → review total (service + add-ons) → bayar via Midtrans
// =============================================================================

/**
 * A) Dynamic Amelia Booking Shortcode
 *    Pake [bcs_booking] di halaman /booking/, bukan [ameliabooking]
 *    Otomatis baca ?service=X dari URL dan pre-select service di Amelia
 */
function bcs_dynamic_booking_shortcode($atts)
{
    $service_id = isset($_GET['service']) ? intval($_GET['service']) : 0;

    if ($service_id > 0) {
        return do_shortcode('[ameliabooking service=' . $service_id . ']');
    }
    return do_shortcode('[ameliabooking]');
}
add_shortcode('bcs_booking', 'bcs_dynamic_booking_shortcode');

/**
 * B) Add custom field "Amelia Service ID" to WooCommerce products
 *    Admin bisa set Amelia service ID di setiap product
 */
function bcs_amelia_product_field()
{
    woocommerce_wp_text_input(array(
        'id' => '_amelia_service_id',
        'label' => 'Amelia Service ID',
        'description' => 'ID service di Amelia (cek di Amelia → Services). Kalau diisi, tombol berubah jadi "Check Availability" di product page dan redirect ke /booking/ setelah add to cart.',
        'type' => 'number',
        'desc_tip' => true,
    ));
}
add_action('woocommerce_product_options_general_product_data', 'bcs_amelia_product_field');

function bcs_amelia_save_product_field($post_id)
{
    $value = isset($_POST['_amelia_service_id']) ? sanitize_text_field($_POST['_amelia_service_id']) : '';
    update_post_meta($post_id, '_amelia_service_id', $value);
}
add_action('woocommerce_process_product_meta', 'bcs_amelia_save_product_field');

/**
 * C) Shop page button URL & text
 *    URL: → product detail page (bukan langsung /booking/, supaya user bisa pilih add-ons)
 *    Text: "Book Now"
 */
function bcs_amelia_product_link($link, $product)
{
    $amelia_id = get_post_meta($product->get_id(), '_amelia_service_id', true);
    if ($amelia_id) {
        return $product->get_permalink(); // ke /product/X/ supaya user bisa pilih add-ons di YITH
    }
    return $link;
}
add_filter('woocommerce_product_add_to_cart_url', 'bcs_amelia_product_link', 10, 2);

function bcs_amelia_button_text($text, $product)
{
    $amelia_id = get_post_meta($product->get_id(), '_amelia_service_id', true);
    if ($amelia_id) {
        return 'Book Now';
    }
    return $text;
}
add_filter('woocommerce_product_add_to_cart_text', 'bcs_amelia_button_text', 10, 2);

/**
 * D) Single product page button text → "Check Availability"
 *    Filter ini khusus untuk single product page (tombol Add to Cart besar di /product/X/)
 */
function bcs_amelia_single_button_text($text)
{
    global $product;
    if ($product && is_a($product, 'WC_Product')) {
        $amelia_id = get_post_meta($product->get_id(), '_amelia_service_id', true);
        if ($amelia_id) {
            return 'Check Availability';
        }
    }
    return $text;
}
add_filter('woocommerce_product_single_add_to_cart_text', 'bcs_amelia_single_button_text');

/**
 * D2) WhatsApp Booking — MENGGANTIKAN add-to-cart + checkout flow
 *     Sesuai instruksi Pak Bayu: client langsung pesan via WhatsApp
 *     dengan format booking yang sudah di-template.
 *
 *     Flow baru: /product/X/ → klik "Book via WhatsApp" → WA terbuka
 *     (Flow lama E/F/checkout tidak lagi aktif untuk Amelia products)
 */
/**
 * D-0) Shop/archive page: ganti tombol "Add to Cart" → "Book Now" link
 *      untuk produk Amelia (mencegah AJAX add-to-cart yang loading terus)
 */
add_filter('woocommerce_loop_add_to_cart_link', 'bcs_amelia_loop_button', 10, 2);
function bcs_amelia_loop_button($button, $product)
{
    $amelia_id = get_post_meta($product->get_id(), '_amelia_service_id', true);
    if (!$amelia_id)
        return $button; // bukan produk Amelia → biarkan default

    // Ganti dengan link biasa ke halaman produk (tanpa class ajax_add_to_cart)
    return sprintf(
        '<a href="%s" class="button product_type_simple" rel="nofollow">Book Now</a>',
        esc_url($product->get_permalink())
    );
}

add_action('woocommerce_before_single_product', 'bcs_setup_whatsapp_booking');
function bcs_setup_whatsapp_booking()
{
    global $product;
    if (!$product)
        return;

    $amelia_id = get_post_meta($product->get_id(), '_amelia_service_id', true);
    if (!$amelia_id)
        return;

    // JANGAN hapus woocommerce_template_single_add_to_cart — supaya YITH add-ons tetap render.
    // Hanya hide quantity + submit button via CSS, biarkan YITH checkboxes tampil.

    // Tambahkan WhatsApp button SETELAH form add-to-cart (priority 31)
    add_action('woocommerce_single_product_summary', 'bcs_render_whatsapp_button', 31);
}

function bcs_render_whatsapp_button()
{
    global $product;
    if (!$product)
        return;

    $product_name = $product->get_name();
    $base_price   = floatval($product->get_price());
    $wa_number    = '6283854168480';

    // Price map untuk JS — supaya bisa kalkulasi total real-time
    $price_map = bcs_addon_price_map();
    ?>
    <div class="bcs-wa-booking-wrap">
        <div class="bcs-wa-booking-info">
            <p>&#x1F4CB; Isi form berikut dan kirim langsung ke WhatsApp admin untuk reservasi cepat!</p>
        </div>
        <a href="#" id="bcs-wa-link" target="_blank" rel="noopener" class="bcs-wa-booking-btn">
            &#x1F4AC; Book via WhatsApp
        </a>
    </div>
    <style>
        /* Hide HANYA quantity + submit button, BIARKAN YITH add-ons tampil */
        .single-product form.cart .quantity,
        .single-product form.cart button.single_add_to_cart_button,
        .single-product form.cart .single_add_to_cart_button,
        .single-product form.cart input[type="submit"] {
            display: none !important;
        }

        .bcs-wa-booking-wrap {
            margin: 24px 0;
        }

        /* Sticky product gallery saat scroll */
        .single-product div.product .woocommerce-product-gallery,
        .single-product div.product .woocommerce-product-gallery.images {
            position: sticky !important;
            top: 100px !important;
            align-self: start !important;
            grid-row: 1 / -1 !important;
        }

        /* Sembunyikan price box WC default (sudah ada di Total Harga kita) */
        .single-product div.product .summary .price {
            display: none !important;
        }

        /* Related products — horizontal flex grid */
        .single-product section.related.products ul.products {
            display: flex !important;
            flex-wrap: wrap;
            gap: 20px;
        }

        .single-product section.related.products ul.products li.product {
            flex: 1 1 calc(50% - 10px);
            max-width: calc(50% - 10px);
            margin: 0 !important;
        }

        @media (min-width: 768px) {
            .single-product section.related.products ul.products li.product {
                flex: 1 1 calc(33.333% - 14px);
                max-width: calc(33.333% - 14px);
            }
        }



        .bcs-wa-booking-info {
            background: rgba(37, 211, 102, 0.08);
            border: 1px solid rgba(37, 211, 102, 0.2);
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 16px;
        }

        .bcs-wa-booking-info p {
            margin: 0;
            color: #ffffff;
            font-size: 14px;
            line-height: 1.6;
            font-family: 'Figtree', sans-serif;
        }

        .bcs-wa-booking-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #25D366;
            color: #fff !important;
            font-family: 'Figtree', sans-serif;
            font-weight: 700;
            font-size: 16px;
            padding: 15px 36px;
            border-radius: 10px;
            text-decoration: none !important;
            transition: all 0.25s ease;
            box-shadow: 0 4px 16px rgba(37, 211, 102, 0.3);
            width: 100%;
            justify-content: center;
        }

        .bcs-wa-booking-btn:hover {
            background: #1da851;
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(37, 211, 102, 0.4);
            color: #fff !important;
        }
    </style>
    <script>
    (function(){
        var basePrice   = <?php echo json_encode($base_price); ?>;
        var productName = <?php echo json_encode($product_name); ?>;
        var waNumber    = <?php echo json_encode($wa_number); ?>;
        var priceMap    = <?php echo json_encode($price_map); ?>;

        function getSelectedAddons() {
            var addons = [];
            /* YITH renders checkboxes/radios inside various containers.
               We look for ANY checked input inside common YITH wrappers. */
            var selectors = [
                '.yith-wapo-container input[type="checkbox"]:checked',
                '.yith-wapo-container input[type="radio"]:checked',
                '.ywapo_group_container input[type="checkbox"]:checked',
                '.ywapo_group_container input[type="radio"]:checked',
                'form.cart .yith_wapo_group input[type="checkbox"]:checked',
                'form.cart .yith_wapo_group input[type="radio"]:checked'
            ];
            var checked = document.querySelectorAll(selectors.join(','));
            checked.forEach(function(input) {
                var rawText = '';
                // Try: <label for="id">
                if (input.id) {
                    var lbl = document.querySelector('label[for="' + input.id + '"]');
                    if (lbl) rawText = lbl.textContent.trim();
                }
                // Fallback: closest label or sibling span
                if (!rawText) {
                    var parent = input.closest('label');
                    if (parent) {
                        rawText = parent.textContent.trim();
                    } else {
                        var sib = input.nextElementSibling;
                        if (sib) rawText = sib.textContent.trim();
                    }
                }

                // Extract price from label text like "(+Rp200.000)" or "(+Rp150.000)"
                var priceFromLabel = 0;
                var priceMatch = rawText.match(/\(\+Rp[.\s]*([\d.,]+)\)/i);
                if (priceMatch) {
                    // Remove dots used as thousands separator, parse number
                    priceFromLabel = parseFloat(priceMatch[1].replace(/\./g, '').replace(',', '.')) || 0;
                }

                // Clean label: remove price suffix like " (+Rp200.000)"
                var label = rawText.replace(/\s*\([^)]*\)\s*$/g, '').trim();

                if (label) addons.push({ label: label, price: priceFromLabel });
            });
            return addons;
        }

        function calcTotal() {
            var total = basePrice;
            var addons = getSelectedAddons();
            addons.forEach(function(addon) {
                total += addon.price;
            });
            return total;
        }

        function formatRupiah(n) {
            return 'Rp' + Math.round(n).toLocaleString('id-ID');
        }

        function updateAll() {
            var addons = getSelectedAddons();
            var total  = calcTotal();

            // Update price display
            var el = document.getElementById('bcs-wa-total');
            if (el) el.innerHTML = '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">Rp</span>' + Math.round(total).toLocaleString('id-ID') + '</bdi></span>';

            // Build WA text (template Pak Bayu, tanpa Promo, dengan Total)
            var text = 'Untuk mempercepat reservasi wajib mengisi form di bawah ini ya! \uD83D\uDE4F\n\n'
                + 'Format Booking\n'
                + 'Nama :\n'
                + 'No. Tlp :\n'
                + 'Alamat :\n'
                + 'Tanggal :\n'
                + 'Jumlah org :\n'
                + 'Jam :\n'
                + 'Jenis Package : ' + productName + '\n';

            if (addons.length > 0) {
                var addonLabels = addons.map(function(a){ return a.label; });
                text += 'Add-ons : ' + addonLabels.join(', ') + '\n';
            }

            text += 'Total : ' + formatRupiah(total) + '\n'
                + 'DP 50%';

            var link = 'https://api.whatsapp.com/send/?phone=' + waNumber + '&text=' + encodeURIComponent(text);
            var btn = document.getElementById('bcs-wa-link');
            if (btn) btn.href = link;
        }

        // Listen for ANY change inside the product form (YITH checkboxes/radios)
        var form = document.querySelector('form.cart');
        if (form) {
            form.addEventListener('change', updateAll);
        }
        // Also listen on document for late-rendered YITH elements
        document.addEventListener('change', function(e) {
            if (e.target.closest && (e.target.closest('.yith-wapo-container') || e.target.closest('.ywapo_group_container'))) {
                updateAll();
            }
        });

        // Initial build
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', updateAll);
        } else {
            updateAll();
        }
    })();
    </script>
    <?php
}

/**
 * E) After add to cart → redirect ke /booking/?service=X
 *    LEGACY: tidak lagi aktif karena flow sudah diganti ke WhatsApp.
 *    Dipertahankan untuk backward compatibility jika ada product
 *    tanpa _amelia_service_id yang masih pakai add-to-cart normal.
 */
function bcs_amelia_redirect_after_add_to_cart($url)
{
    if (isset($_REQUEST['add-to-cart'])) {
        $product_id = absint($_REQUEST['add-to-cart']);
        $amelia_id = get_post_meta($product_id, '_amelia_service_id', true);
        if ($amelia_id) {
            return home_url('/booking/?service=' . $amelia_id);
        }
    }
    return $url;
}
add_filter('woocommerce_add_to_cart_redirect', 'bcs_amelia_redirect_after_add_to_cart');

/**
 * F) Inject JS on /booking/ → setelah Amelia booking selesai,
 *    skip Amelia Payment step dan redirect otomatis ke /checkout/.
 *
 *    WooCommerce cart sudah berisi harga yang BENAR (package + YITH add-ons)
 *    sejak client klik "Check Availability" di product page.
 *    Dengan redirect ke checkout, Midtrans akan charge total yang benar.
 *
 *    Deteksi via:
 *    1. Event 'ameliaBookingSuccess' (Amelia native event)
 *    2. MutationObserver mencari payment/confirmation step muncul
 *    3. Fallback: attach ke tombol "Finish" / "Done" by TEXT
 */
function bcs_amelia_booking_redirect_js()
{
    if (!is_page('booking')) {
        return;
    }

    $redirect_url = home_url('/checkout/');
    ?>
    <style>
        /* Hide Amelia native payment price & replace with info text */
        .am-fs__payments-price .am-fs__payments-price-total,
        .am-fs__payments-price .el-row,
        .am-fs__payments .am-fs__payments-sentence {
            display: none !important;
        }

        .bcs-payment-info {
            padding: 16px 20px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 10px;
            border: 1px solid rgba(255, 38, 0, 0.3);
            margin: 12px 0;
            text-align: center;
        }

        .bcs-payment-info p {
            margin: 0;
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            font-family: 'Figtree', sans-serif;
        }

        .bcs-payment-info .bcs-highlight {
            color: #ff2600;
            font-weight: 700;
        }
    </style>
    <style>
        /* Loading overlay saat redirect ke checkout */
        #bcs-booking-redirect-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999999;
            background: rgba(0, 0, 0, 0.88);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            align-items: center;
            justify-content: center;
            font-family: 'Figtree', sans-serif;
        }

        #bcs-booking-redirect-overlay .bcs-loader {
            text-align: center;
            color: #fff;
        }

        #bcs-booking-redirect-overlay .bcs-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(255, 255, 255, 0.15);
            border-top-color: #ff2600;
            border-radius: 50%;
            animation: bcs-spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes bcs-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <div id="bcs-booking-redirect-overlay">
        <div class="bcs-loader">
            <div class="bcs-spinner"></div>
            <p style="font-size:16px; font-weight:600; margin:0 0 6px;">Preparing Checkout...</p>
            <p style="font-size:13px; color:#aaa; margin:0;">Booking berhasil! Mengarahkan ke pembayaran.</p>
        </div>
    </div>

    <script>
        (function () {
            var redirectUrl = <?php echo json_encode($redirect_url); ?>;
            var redirectDone = false;

            // ── Inject payment info text ke .am-fs__payments-price ──────────────
            function injectPaymentInfo() {
                var priceContainer = document.querySelector('.am-fs__payments-price');
                if (!priceContainer || priceContainer.dataset.bcsInjected) return;

                priceContainer.dataset.bcsInjected = '1';
                // Sembunyikan semua child asli
                Array.from(priceContainer.children).forEach(function (child) {
                    child.style.display = 'none';
                });

                var infoDiv = document.createElement('div');
                infoDiv.className = 'bcs-payment-info';
                infoDiv.innerHTML = '<p>Pembayaran Anda akan dilanjutkan ke<br><span class="bcs-highlight">WooCommerce Midtrans</span></p>';
                priceContainer.appendChild(infoDiv);
                console.log('[BCS] Payment info injected into .am-fs__payments-price');
            }

            // Observer untuk inject saat Payments step muncul
            var paymentObserver = new MutationObserver(injectPaymentInfo);
            paymentObserver.observe(document.body, { childList: true, subtree: true });
            injectPaymentInfo(); // run sekali

            // ── Loading overlay ──────────────────────────────────────────────────
            var overlay = document.getElementById('bcs-booking-redirect-overlay');

            function doRedirect() {
                if (redirectDone) return;
                redirectDone = true;
                console.log('[BCS] Redirecting to checkout...');
                if (overlay) overlay.style.display = 'flex';
                setTimeout(function () {
                    window.location.href = redirectUrl;
                }, 1000);
            }

            // ── Error Detection (HANYA CSS-based, bukan text) ───────────────────
            function hasVisibleError() {
                var errorSelectors = [
                    '.am-alert--error',
                    '.am-alert--danger',
                    '.amelia-error',
                    '.el-message--error',
                    '.el-notification--error',
                ];
                for (var i = 0; i < errorSelectors.length; i++) {
                    var el = document.querySelector(errorSelectors[i]);
                    if (el && el.offsetParent !== null) {
                        console.log('[BCS] Error detected:', errorSelectors[i]);
                        return true;
                    }
                }
                return false;
            }

            // ── Detect Congratulations screen (text + class) ─────────────────────
            function isCongratsVisible() {
                // 1. Check by class
                var classSelectors = [
                    '.am-congratulations',
                    '.am-fs-sb__congratulations',
                    '[class*="congratulation"]',
                    '[class*="congrats"]',
                ];
                for (var i = 0; i < classSelectors.length; i++) {
                    var el = document.querySelector(classSelectors[i]);
                    if (el && el.offsetParent !== null) return true;
                }

                // 2. Check by text "Congratulations" + "Appointment ID"
                var ameliaEl = document.querySelector('#amelia-app-booking, [id*="amelia"], .amelia-app-booking');
                if (ameliaEl) {
                    var text = ameliaEl.innerText || '';
                    if (text.indexOf('Congratulations') !== -1 && text.indexOf('Appointment') !== -1) {
                        return true;
                    }
                }
                return false;
            }

            // ── Strategy 1: Amelia native event ──────────────────────────────────
            window.addEventListener('ameliaBookingSuccess', function () {
                console.log('[BCS] ameliaBookingSuccess event fired');
                doRedirect();
            });

            // ── Strategy 2: MutationObserver — detect congrats screen ────────────
            var observerTriggered = false;
            var observer = new MutationObserver(function () {
                if (redirectDone || observerTriggered) return;

                if (isCongratsVisible()) {
                    observerTriggered = true;
                    console.log('[BCS] Congratulations screen detected');
                    // Tunggu 1 detik lalu redirect (kalau tidak ada error)
                    setTimeout(function () {
                        if (!hasVisibleError()) {
                            doRedirect();
                        }
                    }, 1000);
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });

            // ── Strategy 3: Attach ke tombol "Finish" ────────────────────────────
            // Fallback terakhir — kalau user klik Finish, redirect
            var finishTexts = ['finish', 'done', 'selesai'];

            function attachToFinishBtn() {
                var buttons = document.querySelectorAll(
                    'button[class*="am-button"], .am-button--primary, .am-button'
                );
                buttons.forEach(function (btn) {
                    if (btn.dataset.bcsAttached) return;
                    var txt = (btn.textContent || '').trim().toLowerCase();
                    if (finishTexts.indexOf(txt) !== -1) {
                        btn.dataset.bcsAttached = '1';
                        console.log('[BCS] Attached to Finish button');
                        btn.addEventListener('click', function () {
                            // Tunggu 500ms setelah klik — beri waktu Amelia process
                            setTimeout(function () {
                                if (!hasVisibleError()) {
                                    doRedirect();
                                }
                            }, 500);
                        });
                    }
                });
            }

            // Re-scan tombol Finish setiap kali DOM berubah
            var btnObserver = new MutationObserver(attachToFinishBtn);
            btnObserver.observe(document.body, { childList: true, subtree: true });
            attachToFinishBtn(); // run sekali saat load
        })();
    </script>
    <?php
}
add_action('wp_footer', 'bcs_amelia_booking_redirect_js');

// =============================================================================
//  AMELIA ↔ WOOCOMMERCE PAYMENT LINK
//  -------------------------------------------------------------------------
//  Menghubungkan Amelia booking ID dengan WooCommerce Order sehingga:
//  1. Saat Midtrans berhasil → WC order status = "processing"
//  2. PHP hook otomatis update Amelia booking status → "approved"
//  3. PDF Invoice plugin generate & kirim invoice ke client + admin
//
//  Flow:
//    Amelia booking saved → booking_id disimpan ke WC Session
//    → WC checkout → booking_id attach ke WC Order meta
//    → Midtrans callback → WC order processing
//    → Hook: Amelia booking approved + invoice sent
// =============================================================================

/**
 * Step A: Saat Amelia booking berhasil dibuat, simpan booking_id ke WC session.
 * Dipakai di Step B untuk di-attach ke WC order saat checkout.
 */
add_action('AmeliaBookingAddedBeforeNotify', 'bcs_store_amelia_booking_id_in_session', 5);
function bcs_store_amelia_booking_id_in_session($reservation)
{
    $booking_id = bcs_first_value($reservation, [
        'booking.id',
        'bookings.0.id',
        'appointment.bookings.0.id',
    ], 0);

    if (!$booking_id)
        return;

    // Pastikan WC session tersedia (Amelia hook bisa fire di REST context)
    if (function_exists('wc_load_cart')) {
        wc_load_cart();
    }

    if (function_exists('WC') && WC()->session) {
        WC()->session->set('bcs_amelia_booking_id', intval($booking_id));
        if (defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG) {
            error_log('[BCS-PayLink] Amelia booking_id=' . $booking_id . ' saved to WC session');
        }
    }
}

/**
 * Step B: Saat WooCommerce order dibuat (checkout submit),
 * baca booking_id dari WC session → simpan ke order meta.
 * Ini yang dipakai Step C untuk update Amelia setelah payment.
 */
add_action('woocommerce_checkout_create_order', 'bcs_attach_amelia_id_to_order', 10, 2);
function bcs_attach_amelia_id_to_order($order, $data)
{
    if (!function_exists('WC') || !WC()->session)
        return;

    $booking_id = intval(WC()->session->get('bcs_amelia_booking_id'));
    if (!$booking_id)
        return;

    $order->update_meta_data('_amelia_booking_id', $booking_id);

    if (defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG) {
        error_log('[BCS-PayLink] WC Order: attached Amelia booking_id=' . $booking_id);
    }
}

/**
 * Step C: Setelah WooCommerce order status berubah ke "processing" (Midtrans paid)
 * atau "completed", update status Amelia booking → "approved" di database
 * DAN kirim data konfirmasi ke Google Sheets.
 */
add_action('woocommerce_order_status_processing', 'bcs_update_amelia_status_after_payment', 10, 1);
add_action('woocommerce_order_status_completed', 'bcs_update_amelia_status_after_payment', 10, 1);

function bcs_update_amelia_status_after_payment($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order)
        return;

    $booking_id = intval($order->get_meta('_amelia_booking_id'));
    if (!$booking_id)
        return;

    global $wpdb;
    $wc_status = $order->get_status(); // 'processing' atau 'completed'

    // 1. Update customer booking status → approved
    $updated = $wpdb->update(
        $wpdb->prefix . 'amelia_customer_bookings',
        ['status' => 'approved'],
        ['id' => $booking_id],
        ['%s'],
        ['%d']
    );

    // 2. Update payment record di amelia_payments table
    $wpdb->update(
        $wpdb->prefix . 'amelia_payments',
        [
            'status' => 'paid',
            'amount' => floatval($order->get_total()),
        ],
        ['customerBookingId' => $booking_id],
        ['%s', '%f'],
        ['%d']
    );

    // 3. Kirim data KONFIRMASI ke Google Sheets
    //    Ambil detail booking dari Amelia DB (lebih lengkap dari WC meta)
    bcs_send_confirmed_order_to_sheets($order, $booking_id, $wc_status);

    // 4. Bersihkan session supaya tidak bocor ke booking berikutnya
    if (function_exists('WC') && WC()->session) {
        WC()->session->__unset('bcs_amelia_booking_id');
    }

    if (defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG) {
        error_log('[BCS-PayLink] WC Order #' . $order_id . ' → Amelia booking #' . $booking_id . ' updated to approved. DB rows=' . $updated);
    }
}

/**
 * Kirim data order yang sudah dikonfirmasi ke Google Sheets.
 * Dipanggil saat WC order status → processing / completed.
 * Data diambil langsung dari Amelia DB untuk akurasi maksimal.
 */
function bcs_send_confirmed_order_to_sheets($order, $booking_id, $wc_status)
{
    $webhook_url = BCS_GOOGLE_SHEETS_WEBHOOK_URL;
    if (empty($webhook_url))
        return;

    global $wpdb;

    // Ambil data booking + customer dari Amelia DB
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT
            cb.id            AS booking_id,
            cb.appointmentId AS appointment_id,
            cb.price         AS booking_price,
            cb.status        AS booking_status,
            c.firstName      AS first_name,
            c.lastName       AS last_name,
            c.email          AS email,
            c.phone          AS phone
         FROM {$wpdb->prefix}amelia_customer_bookings cb
         LEFT JOIN {$wpdb->prefix}amelia_users c ON c.id = cb.customerId
         WHERE cb.id = %d",
        $booking_id
    ), ARRAY_A);

    if (!$row) {
        if (defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG) {
            error_log('[BCS-Sheets-Confirm] Booking row not found for id=' . $booking_id);
        }
        return;
    }

    // Ambil detail appointment (waktu, service)
    $appointment_id = intval($row['appointment_id']);
    $appt = $wpdb->get_row($wpdb->prepare(
        "SELECT
            a.bookingStart,
            a.bookingEnd,
            s.name           AS service_name,
            cat.name         AS category_name
         FROM {$wpdb->prefix}amelia_appointments a
         LEFT JOIN {$wpdb->prefix}amelia_services s   ON s.id = a.serviceId
         LEFT JOIN {$wpdb->prefix}amelia_categories cat ON cat.id = s.categoryId
         WHERE a.id = %d",
        $appointment_id
    ), ARRAY_A);

    $booking_start = $appt ? $appt['bookingStart'] : '';
    $booking_end = $appt ? $appt['bookingEnd'] : '';
    $service_name = $appt ? $appt['service_name'] : '';
    $category_name = $appt ? $appt['category_name'] : '';

    // Total dari WC (sudah include YITH add-ons)
    $total = floatval($order->get_total());

    // ── Bangun "extras" string: Add-ons | Order Notes ───────────────────
    // (Service name TIDAK dimasukkan di sini — sudah ada di kolom sendiri)
    $theme_parts = [];

    // 1. YITH Add-on labels dari WC order items
    $addon_labels = [];
    foreach ($order->get_items() as $item) {
        $product_name = $item->get_name(); // nama product (untuk di-skip)
        $meta_data = $item->get_meta_data();
        foreach ($meta_data as $meta) {
            $meta_key = $meta->key;
            $meta_value = $meta->value;

            // Skip internal WC/YITH meta (dimulai dengan _)
            if (strpos($meta_key, '_') === 0)
                continue;

            // Skip kalau value bukan string atau kosong
            if (empty($meta_value) || !is_string($meta_value))
                continue;

            // Skip kalau value = nama product itu sendiri (duplikat)
            if (trim($meta_value) === trim($product_name))
                continue;

            // Skip meta WC standar (bukan add-on)
            $skip_keys = ['pa_', 'quantity', 'Qty'];
            $is_skip = false;
            foreach ($skip_keys as $sk) {
                if (stripos($meta_key, $sk) !== false) {
                    $is_skip = true;
                    break;
                }
            }
            if ($is_skip)
                continue;

            $addon_labels[] = $meta_value;
        }
    }
    if (!empty($addon_labels)) {
        $theme_parts[] = implode(' | ', array_unique($addon_labels));
    }

    // 2. Order notes (Additional Information — tema foto dari client)
    //    Multiple fallback karena WC bisa simpan di tempat berbeda (HPOS vs legacy)
    $customer_note = $order->get_customer_note();

    // Fallback 1: post_excerpt (legacy storage)
    if (empty($customer_note)) {
        $customer_note = get_post_field('post_excerpt', $order->get_id());
    }

    // Fallback 2: order meta
    if (empty($customer_note)) {
        $customer_note = $order->get_meta('_order_notes');
    }
    if (empty($customer_note)) {
        $customer_note = $order->get_meta('customer_note');
    }

    if (!empty($customer_note)) {
        $theme_parts[] = trim($customer_note);
    }

    $extras_str = implode(' | ', $theme_parts);

    // Debug: log apa yang terdeteksi
    if (defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG) {
        error_log('[BCS-Sheets-Confirm] Addon labels: ' . wp_json_encode($addon_labels));
        error_log('[BCS-Sheets-Confirm] Customer note: "' . ($customer_note ?: '(empty)') . '"');
        error_log('[BCS-Sheets-Confirm] Extras string: "' . $extras_str . '"');
    }

    // Bangun payload
    $payload = [
        'appointment' => [
            'bookingStart' => $booking_start,
            'bookingEnd' => $booking_end,
            'price' => $total,
            'status' => 'Booked',
            'wc_order_id' => $order->get_id(),
            'customer' => [
                'firstName' => $row['first_name'],
                'lastName' => $row['last_name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
            ],
            'service' => [
                'name' => $service_name,
                'category' => ['name' => $category_name],
            ],
            'extras' => $extras_str,
        ],
    ];

    if (defined('BCS_SHEETS_DEBUG') && BCS_SHEETS_DEBUG) {
        error_log('[BCS-Sheets-Confirm] Sending confirmed order to Sheets: ' . wp_json_encode($payload));
    }

    wp_remote_post($webhook_url, [
        'method' => 'POST',
        'timeout' => 15,
        'blocking' => false,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => wp_json_encode($payload),
    ]);
}
