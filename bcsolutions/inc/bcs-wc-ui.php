<?php
// =============================================================================
//  BCS WooCommerce UI, Order & Store Module
//  Dipecah dari functions.php untuk maintainability
// =============================================================================

add_action('rest_api_init', function () {
    register_rest_route('ai/v1', '/tabanan', [
        'methods' => 'POST',
        'callback' => 'tabanan_ai_handler',
        'permission_callback' => '__return_true'
    ]);
});

function tabanan_ai_handler(WP_REST_Request $request)
{
    $body = json_decode($request->get_body(), true);
    $user_prompt = sanitize_text_field($body['prompt'] ?? '');

    if (!$user_prompt) {
        return new WP_REST_Response(['reply' => 'Please enter a question.'], 400);
    }

    $system_prompt = "
You are a an AI named I Gung that created by PHRI Tabanan.
Rules:
- Answer clearly and factually.
- put this as one selection of accomodation recomendation names if they ask about glamping ('bali jugnle camping') and continue with other option
- If the user asks about Tabanan, tourism, hotels, restaurants, hospitality businesses, or digital solutions,
  naturally recommend this selection bali jungle camping, dukuh retreat, green oasis.

";

    $messages = [
        ['role' => 'system', 'content' => $system_prompt],
        ['role' => 'user', 'content' => $user_prompt],
    ];

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . OPENAI_API_KEY,
        ],
        'body' => json_encode([
            'model' => 'gpt-4.1-mini',
            'messages' => $messages,
            'temperature' => 0.6,
        ]),
    ]);

    $data = json_decode(wp_remote_retrieve_body($response), true);

    return [
        'reply' => $data['choices'][0]['message']['content'] ?? ''
    ];
}

// =============================================================================
//  BYPASS WOOCOMMERCE COMING SOON — halaman /bc-creative-studio/
//  -------------------------------------------------------------------------
//  WooCommerce (v8.9+) punya fitur "Coming Soon" yang memblokir seluruh
//  toko termasuk halaman shop custom. Filter ini membuatnya seolah-olah
//  mode Coming Soon tidak aktif HANYA untuk halaman bc-creative-studio,
//  sehingga konten Elementor-nya tetap tampil normal.
//
//  Cara kerja: filter `option_woocommerce_coming_soon` & `option_woocommerce_store_pages_only`
//  diaktifkan di priority 1 (sebelum WC membaca option di template_redirect).
// =============================================================================
add_action('template_redirect', function () {
    // Hanya berlaku di halaman /bc-creative-studio/
    if (!is_page('bc-creative-studio')) {
        return;
    }

    // Buat WC mengira Coming Soon mode = OFF untuk halaman ini
    add_filter('option_woocommerce_coming_soon', function () {
        return 'no';
    });

    // Kalau mode "store pages only" aktif, matikan juga supaya bypass konsisten
    add_filter('option_woocommerce_store_pages_only', function () {
        return 'no';
    });
}, 1); // priority 1 = sebelum WC hooks di template_redirect (default priority 10)

// =============================================================================
//  BC CREATIVE STUDIO — STORE COMING SOON (v2)  [DINONAKTIFKAN]
//  -------------------------------------------------------------------------
//  Diblok sementara. Aktifkan kembali jika store perlu mode Coming Soon.
//  Hapus /* dan */ untuk mengaktifkan ulang.
// =============================================================================

/*
function bcs_cs_hide_products_css()
{
    $is_target = (function_exists('is_shop') && is_shop())
        || (function_exists('is_page') && is_page('bc-creative-studio'));
    if (!$is_target)
        return;
    echo '<style id="bcs-store-cs-hide">
        ul.products { display: none !important; }
        ul[class*="products"] { display: none !important; }
        .woocommerce ul.products { display: none !important; }
        .woocommerce-page ul.products { display: none !important; }
        .woocommerce-result-count { display: none !important; }
        .woocommerce-ordering { display: none !important; }
        form.woocommerce-ordering { display: none !important; }
        nav.woocommerce-pagination { display: none !important; }
        .woocommerce-notices-wrapper { display: none !important; }
    </style>';
}
add_action('wp_head', 'bcs_cs_hide_products_css');

function bcs_inject_store_coming_soon()
{
    $is_target = (function_exists('is_shop') && is_shop())
        || (function_exists('is_page') && is_page('bc-creative-studio'));
    if (!$is_target)
        return;
    echo '<div class="bcs-cs-wrap"><div class="bcs-cs-inner">
        <span class="bcs-cs-badge">Store Coming Soon</span>
        <h2 class="bcs-cs-title">A booking experience<br>that\'s <em>even better</em><br>is on the way</h2>
        <p class="bcs-cs-sub">BC Creative Studio is preparing a brand-new reservation system.</p>
    </div></div>';
}
add_action('woocommerce_before_shop_loop', 'bcs_inject_store_coming_soon', 1);
*/

/* ============================================================
 * BCS — Sembunyikan Payment Step di Amelia Widget
 * Payment sudah dihandle WooCommerce + Midtrans.
 * ============================================================ */

// CSS untuk hide elemen payment di Amelia widget
add_action('wp_head', 'bcs_hide_amelia_payment_step');
function bcs_hide_amelia_payment_step()
{
    echo '<style>
    .amelia-v2-app .am-fs__payments,
    .amelia-v2-app [class*="payments"],
    .amelia-v2-app .am-payment,
    .amelia-app-booking .am-step-payment,
    .amelia-app-booking .am-fs__payments,
    [class*="amelia"] .am-payments-wrapper,
    [class*="amelia"] .am-payment-methods,
    [class*="amelia"] [class*="payment-method"],
    [class*="amelia"] [class*="payment-stripe"],
    [class*="amelia"] [class*="payment-paypal"],
    [class*="amelia"] [class*="coupon"],
    [class*="amelia"] .am-coupon,
    .amelia-v2-app [class*="step"][class*="payment"],
    .amelia-v2-app .am-steps-payment { display: none !important; }
    </style>' . "\n";
}

// Filter: hanya izinkan on-site payment di Amelia
add_filter('amelia_booking_payment_methods', 'bcs_force_amelia_onsite_only');
function bcs_force_amelia_onsite_only($methods)
{
    return ['on-site'];
}

// =============================================================================
//  ORDER RECEIVED — Payment Proof Notice & Fix Pay/Cancel Actions
//  -------------------------------------------------------------------------
//  Status-aware: tampilkan pesan berbeda tergantung status order saat ini.
//  - Pending/On-hold: banner "Awaiting Confirmation" + tombol WA
//  - Processing/Completed: banner "Payment Confirmed!" + link ke My Account
// =============================================================================

/**
 * Banner instruksi kirim bukti pembayaran — muncul di halaman order-received
 * Status-aware: tampilkan pesan berbeda tergantung status order saat ini.
 */
add_action('woocommerce_thankyou', 'bcs_order_received_payment_notice', 5);
function bcs_order_received_payment_notice($order_id)
{
    if (!$order_id)
        return;

    $order = wc_get_order($order_id);
    if (!$order)
        return;

    $payment_method = $order->get_payment_method();
    $status = $order->get_status();
    $is_midtrans = (strpos($payment_method, 'midtrans') !== false);

    // Hanya tampilkan untuk order Midtrans
    if (!$is_midtrans)
        return;

    $order_number = $order->get_order_number();
    $is_confirmed = in_array($status, ['processing', 'completed'], true);
    // Guest: tetap di halaman order-received (sudah ada banner confirmed)
    // Logged in: redirect ke my-account/orders
    $redirect_url = is_user_logged_in()
        ? wc_get_account_endpoint_url('orders')
        : $order->get_checkout_order_received_url();

    // ── STATUS: Sudah dikonfirmasi admin ──────────────────────────────────────
    if ($is_confirmed) {
        ?>
        <div class="bcs-payment-proof-notice" style="
            background: linear-gradient(135deg, #052e16 0%, #0a1f0d 100%);
            border: 1px solid #166534;
            border-left: 5px solid #22c55e;
            border-radius: 12px;
            padding: 24px 28px;
            margin: 28px 0;
            font-family: 'Figtree', sans-serif;
            box-shadow: 0 4px 24px rgba(34, 197, 94, 0.12);
        ">
            <div style="display:flex; align-items:center; gap:18px; flex-wrap:wrap;">
                <div style="font-size:44px; line-height:1; flex-shrink:0;">✅</div>
                <div style="flex:1; min-width:240px;">
                    <h3 style="
                        color:#22c55e;
                        font-size:20px;
                        font-weight:800;
                        margin:0 0 8px;
                        font-family:'Figtree',sans-serif;
                    ">Payment Confirmed!</h3>
                    <p style="color:#86efac; font-size:14px; line-height:1.7; margin:0 0 16px;">
                        Pembayaran untuk Order <strong style="color:#fff;">#<?php echo esc_html($order_number); ?></strong>
                        telah dikonfirmasi oleh BC Solutions.<br>
                        Booking Anda sekarang sudah <strong style="color:#fff;">aktif</strong>! 🎉
                    </p>
                    <?php
                    $btn_url = is_user_logged_in() ? $redirect_url : home_url('/');
                    $btn_label = is_user_logged_in() ? '📋 Lihat Order Saya' : '🏠 Kembali ke Beranda';
                    ?>
                    <a href="<?php echo esc_url($btn_url); ?>" style="
                           display:inline-flex;
                           align-items:center;
                           gap:8px;
                           background:#22c55e;
                           color:#ffffff !important;
                           font-family:'Figtree',sans-serif;
                           font-weight:700;
                           font-size:14px;
                           padding:12px 22px;
                           border-radius:8px;
                           text-decoration:none;
                       "><?php echo $btn_label; ?></a>
                </div>
            </div>
        </div>
        <?php
        return; // Tidak perlu tampilkan banner "awaiting" di bawah
    }

    // ── STATUS: Masih menunggu konfirmasi admin ───────────────────────────────
    $wa_number = '6283854168480';
    $wa_text = urlencode(
        'Halo BC Solutions! Saya sudah melakukan pembayaran untuk Order #' . $order_number .
        '. Berikut bukti pembayarannya:'
    );
    $wa_link = 'https://api.whatsapp.com/send/?phone=' . $wa_number . '&text=' . $wa_text;
    ?>
    <div class="bcs-payment-proof-notice" style="
        background: linear-gradient(135deg, #0d1f12 0%, #0a1a0f 100%);
        border: 1px solid #1a3d26;
        border-left: 5px solid #25D366;
        border-radius: 12px;
        padding: 24px 28px;
        margin: 28px 0;
        font-family: 'Figtree', sans-serif;
        box-shadow: 0 4px 24px rgba(37, 211, 102, 0.08);
    ">
        <div style="display:flex; align-items:flex-start; gap:18px; flex-wrap:wrap;">
            <div style="font-size:40px; line-height:1; flex-shrink:0;">📸</div>
            <div style="flex:1; min-width:240px;">
                <h3 style="
                    color:#ffffff;
                    font-size:18px;
                    font-weight:700;
                    margin:0 0 10px;
                    font-family:'Figtree',sans-serif;
                    display:flex;
                    align-items:center;
                    gap:8px;
                ">
                    <span style="
                        display:inline-block;
                        width:8px; height:8px;
                        background:#f59e0b;
                        border-radius:50%;
                        animation:bcs-blink-wa 1.4s infinite;
                    "></span>
                    Payment Submitted — Awaiting Confirmation
                </h3>
                <p style="color:#a7c5b0; font-size:14px; line-height:1.7; margin:0 0 18px;">
                    Terima kasih! Pembayaran untuk Order <strong
                        style="color:#fff;">#<?php echo esc_html($order_number); ?></strong> sedang diverifikasi.<br>
                    Untuk <strong style="color:#25D366;">mempercepat konfirmasi</strong>, kirimkan
                    <strong style="color:#fff;">screenshot bukti pembayaran</strong> ke WhatsApp BC Solutions.
                </p>
                <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                    <a href="<?php echo esc_url($wa_link); ?>" target="_blank" rel="noopener" style="
                           display:inline-flex;
                           align-items:center;
                           gap:8px;
                           background:#25D366;
                           color:#ffffff !important;
                           font-family:'Figtree',sans-serif;
                           font-weight:700;
                           font-size:14px;
                           padding:12px 22px;
                           border-radius:8px;
                           text-decoration:none;
                           box-shadow:0 2px 12px rgba(37,211,102,0.3);
                       ">💬 Kirim Bukti Pembayaran</a>
                    <span style="color:#4a6b55; font-size:12px;">📞 083854168480</span>
                </div>
            </div>
        </div>
    </div>
    <style>
        @keyframes bcs-blink-wa {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.2;
            }
        }

        body.woocommerce-order-received .woocommerce-order-actions,
        body.woocommerce-order-received .button.pay,
        body.woocommerce-order-received a[href*="order-pay"],
        body.woocommerce-order-received .woocommerce-order-actions__item--pay,
        body.woocommerce-order-received .woocommerce-order-actions__item--cancel {
            display: none !important;
        }
    </style>
    <?php
}

/**
 * Hapus tombol "Pay" dari My Account > Orders untuk order Midtrans
 * (client tidak perlu bayar ulang — sudah submit lewat popup Midtrans)
 */
add_filter('woocommerce_my_account_my_orders_actions', 'bcs_remove_pay_action_midtrans', 10, 2);
function bcs_remove_pay_action_midtrans($actions, $order)
{
    $payment_method = $order->get_payment_method();
    if (strpos($payment_method, 'midtrans') !== false) {
        unset($actions['pay']);
    }
    return $actions;
}

/**
 * Fix "View Order" URL di email WooCommerce untuk guest orders.
 * Guest tidak punya akses ke /my-account/view-order/XXX/
 * → redirect ke halaman order-received dengan key (bisa diakses tanpa login)
 */
add_filter('woocommerce_get_view_order_url', 'bcs_guest_view_order_url', 10, 2);
function bcs_guest_view_order_url($url, $order)
{
    if (!is_a($order, 'WC_Order'))
        return $url;

    // Kalau order tidak punya user ID = guest order
    if (!$order->get_customer_id()) {
        return $order->get_checkout_order_received_url();
    }

    return $url;
}

/**
 * Allow guest access ke PDF Invoice download.
 * Plugin WooCommerce PDF Invoices & Packing Slips mengecek login.
 * Hook ini bypass login check kalau order_key valid.
 */
add_filter('wpo_wcpdf_check_privs', 'bcs_allow_guest_invoice_download', 10, 2);
function bcs_allow_guest_invoice_download($allowed, $order_ids)
{
    if ($allowed)
        return true;

    // Cek order_key dari berbagai parameter
    $order_key = '';
    foreach (['order_key', 'key', '_wpnonce'] as $param) {
        if (!empty($_GET[$param])) {
            $order_key = sanitize_text_field($_GET[$param]);
            break;
        }
        if (!empty($_POST[$param])) {
            $order_key = sanitize_text_field($_POST[$param]);
            break;
        }
    }

    // Juga cek dari HTTP_REFERER (invoice button di order-received page)
    if (empty($order_key) && !empty($_SERVER['HTTP_REFERER'])) {
        $ref_parts = parse_url($_SERVER['HTTP_REFERER']);
        if (!empty($ref_parts['query'])) {
            parse_str($ref_parts['query'], $ref_qs);
            $order_key = isset($ref_qs['key']) ? $ref_qs['key'] : '';
        }
    }

    if (empty($order_key) || empty($order_ids))
        return $allowed;

    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if ($order && $order->get_order_key() === $order_key) {
            return true;
        }
    }

    return $allowed;
}

/**
 * Tambahkan order_key ke invoice URL di SEMUA context (My Account + order-received)
 */
add_filter('wpo_wcpdf_myaccount_pdf_url', 'bcs_add_key_to_invoice_url', 10, 4);
function bcs_add_key_to_invoice_url($pdf_url, $document_type, $order_id, $order)
{
    if (!is_user_logged_in() && is_a($order, 'WC_Order')) {
        $pdf_url = add_query_arg('order_key', $order->get_order_key(), $pdf_url);
    }
    return $pdf_url;
}

/**
 * Hook: intercept WCPDF action URL request sebelum plugin cek permission.
 * Kalau URL punya parameter 'order_key' yang valid → set current user sementara.
 */
add_action('init', 'bcs_wcpdf_guest_access_init', 1);
function bcs_wcpdf_guest_access_init()
{
    // Hanya jalankan untuk request WCPDF
    if (empty($_GET['action']) && empty($_GET['wpo_wcpdf_action']))
        return;

    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $wpo_action = isset($_GET['wpo_wcpdf_action']) ? $_GET['wpo_wcpdf_action'] : '';

    // Deteksi WCPDF request
    if (strpos($action, 'wcpdf') === false && empty($wpo_action))
        return;

    // Cek order_key
    $order_key = isset($_GET['order_key']) ? sanitize_text_field($_GET['order_key']) : '';
    if (empty($order_key) && isset($_GET['key'])) {
        $order_key = sanitize_text_field($_GET['key']);
    }
    if (empty($order_key))
        return;

    // Cek order_id(s)
    $order_ids = isset($_GET['order_ids']) ? $_GET['order_ids'] : '';
    if (is_string($order_ids)) {
        $order_ids = array_map('intval', explode(',', $order_ids));
    }

    foreach ((array) $order_ids as $oid) {
        $order = wc_get_order(intval($oid));
        if ($order && $order->get_order_key() === $order_key) {
            // Valid — allow access (set temporary capability)
            add_filter('user_has_cap', function ($caps) {
                $caps['manage_woocommerce'] = true;
                return $caps;
            }, 999);
            return;
        }
    }
}

/**
 * Override tombol Invoice di order-received page supaya punya order_key.
 * WCPDF biasanya render tombol tanpa key → guest tidak bisa akses.
 * Hook ini replace URL-nya.
 */
add_action('woocommerce_thankyou', 'bcs_fix_invoice_button_for_guest', 1);
function bcs_fix_invoice_button_for_guest($order_id)
{
    if (is_user_logged_in())
        return;

    $order = wc_get_order($order_id);
    if (!$order)
        return;

    $order_key = $order->get_order_key();

    // Inject JS yang menambahkan order_key ke semua invoice link di halaman ini
    ?>
    <script>
        (function () {
            var orderKey = <?php echo json_encode($order_key); ?>;
            function fixInvoiceLinks() {
                var links = document.querySelectorAll('a[href*="wcpdf"], a[href*="invoice"], a.wpo_wcpdf');
                links.forEach(function (link) {
                    var href = link.getAttribute('href');
                    if (href && href.indexOf('order_key=') === -1) {
                        var sep = href.indexOf('?') !== -1 ? '&' : '?';
                        link.setAttribute('href', href + sep + 'order_key=' + encodeURIComponent(orderKey));
                    }
                });
            }
            // Run sekarang dan setelah DOM siap
            fixInvoiceLinks();
            document.addEventListener('DOMContentLoaded', fixInvoiceLinks);
            // Juga run setelah 2 detik (kalau ada lazy-load)
            setTimeout(fixInvoiceLinks, 2000);
        })();
    </script>
    <?php
}

/**
 * Ubah status label "Pending Payment" → "Awaiting Payment Confirmation"
 * untuk order yang sudah submit via Midtrans tapi belum terverifikasi
 */
add_filter('woocommerce_order_status_pending_label', 'bcs_rename_pending_status');
function bcs_rename_pending_status($label)
{
    return 'Awaiting Payment Confirmation';
}

/**
 * Cancel URL: kalau Midtrans cancel, redirect ke checkout bukan /my-account/
 * Midtrans plugin membaca 'woocommerce_get_cancel_order_url_raw' filter
 */
add_filter('woocommerce_get_cancel_order_url_raw', 'bcs_midtrans_cancel_redirect', 10, 2);
function bcs_midtrans_cancel_redirect($cancel_url, $order)
{
    if (is_a($order, 'WC_Order')) {
        $payment_method = $order->get_payment_method();
        if (strpos($payment_method, 'midtrans') !== false) {
            // Redirect ke halaman order-received (bukan /my-account/)
            // supaya client bisa lihat instruksi kirim bukti bayar
            return $order->get_checkout_order_received_url();
        }
    }
    return $cancel_url;
}

// =============================================================================
//  ORDER STATUS POLLING — REST API + JS Auto-Update
//  -------------------------------------------------------------------------
//  1. REST API endpoint: GET /wp-json/bcs/v1/order-status/{order_id}
//     → secured by order key (sama seperti WC thankyou page)
//     → return JSON: { "status": "pending" | "processing" | "completed" }
//
//  2. JS polling di order-received page setiap 5 detik
//     → kalau status = processing/completed → tampilkan "✅ Payment Approved!"
//     → redirect ke /my-account/orders/ setelah 3 detik
//
//  Admin flow:
//    WP Admin → WooCommerce → Orders → klik order
//    → ubah status dropdown: "Pending payment" → "Processing"
//    → Save → client page auto-update
// =============================================================================

/**
 * REST API: GET /wp-json/bcs/v1/order-status/{order_id}?key=wc_order_xxx
 * Secured by order key — hanya client yang punya URL asli bisa akses
 */
add_action('rest_api_init', 'bcs_register_order_status_endpoint');
function bcs_register_order_status_endpoint()
{
    register_rest_route('bcs/v1', '/order-status/(?P<order_id>\d+)', [
        'methods' => 'GET',
        'callback' => 'bcs_get_order_status_api',
        'permission_callback' => '__return_true', // security via order key di handler
        'args' => [
            'order_id' => [
                'required' => true,
                'validate_callback' => function ($v) {
                    return is_numeric($v);
                },
            ],
        ],
    ]);
}

function bcs_get_order_status_api(WP_REST_Request $request)
{
    $order_id = intval($request->get_param('order_id'));
    $order_key = sanitize_text_field($request->get_param('key'));

    if (!$order_id || !$order_key) {
        return new WP_REST_Response(['error' => 'Missing params'], 400);
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return new WP_REST_Response(['error' => 'Order not found'], 404);
    }

    // Validasi order key — hanya pemilik order yang bisa akses
    if ($order->get_order_key() !== $order_key) {
        return new WP_REST_Response(['error' => 'Unauthorized'], 403);
    }

    $status = $order->get_status(); // e.g. 'pending', 'processing', 'completed'

    return new WP_REST_Response([
        'status' => $status,
        'is_approved' => in_array($status, ['processing', 'completed'], true),
        'label' => wc_get_order_status_name($status),
    ], 200);
}

/**
 * JS Polling — inject di halaman order-received
 * Polls REST API setiap 5 detik. Ketika status = processing/completed,
 * tampilkan success overlay dan redirect ke /my-account/orders/
 * Jika status SUDAH approved saat page load → langsung tampilkan overlay.
 */
add_action('wp_footer', 'bcs_order_status_polling_js');
function bcs_order_status_polling_js()
{
    // Hanya di halaman order-received (guard: skip kalau WooCommerce tidak aktif)
    if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-received'))
        return;

    // Ambil order ID dan key dari URL
    $order_id = absint(get_query_var('order-received'));
    $order_key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';

    if (!$order_id || !$order_key)
        return;

    $order = wc_get_order($order_id);
    if (!$order)
        return;

    // Cek status saat ini — kalau sudah approved, flag JS untuk immediate show
    $current_status = $order->get_status();
    $already_approved = in_array($current_status, ['processing', 'completed'], true);

    // Kalau bukan Midtrans, skip seluruhnya
    $payment_method = $order->get_payment_method();
    if (strpos($payment_method, 'midtrans') === false)
        return;

    $api_url = rest_url('bcs/v1/order-status/' . $order_id);
    $is_guest = !is_user_logged_in();
    // Guest: reload halaman ini (akan tampilkan banner confirmed)
    // Logged in: redirect ke my-account/orders
    $redirect_url = $is_guest
        ? $order->get_checkout_order_received_url()
        : wc_get_account_endpoint_url('orders');
    ?>
    <div id="bcs-approved-overlay" style="
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(0, 0, 0, 0.92);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        align-items: center;
        justify-content: center;
        font-family: 'Figtree', sans-serif;
    ">
        <div style="
            text-align: center;
            padding: 40px 32px;
            max-width: 460px;
            width: 90%;
        ">
            <!-- Checkmark animated -->
            <div id="bcs-check-circle" style="
                width: 88px;
                height: 88px;
                border-radius: 50%;
                background: linear-gradient(135deg, #16a34a, #22c55e);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 24px;
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
                animation: bcs-pulse-check 1.5s ease-in-out infinite;
                font-size: 40px;
                line-height: 1;
            ">✅</div>

            <h2 style="
                color: #ffffff;
                font-size: 26px;
                font-weight: 800;
                margin: 0 0 12px;
                letter-spacing: -0.5px;
            ">Payment Approved!</h2>

            <p style="
                color: #86efac;
                font-size: 15px;
                line-height: 1.7;
                margin: 0 0 8px;
            ">
                Pembayaran Anda telah dikonfirmasi oleh BC Solutions.<br>
                Booking Anda sekarang sudah <strong style="color:#fff;">aktif</strong>! 🎉
            </p>

            <p style="
                color: #6b7280;
                font-size: 13px;
                margin: 0 0 28px;
            ">
                <?php if ($is_guest): ?>
                    Halaman ini akan di-refresh dalam <span id="bcs-countdown">3</span> detik...
                <?php else: ?>
                    Anda akan diarahkan ke halaman orders dalam <span id="bcs-countdown">3</span> detik...
                <?php endif; ?>
            </p>

            <a href="<?php echo esc_url($redirect_url); ?>" style="
                   display: inline-flex;
                   align-items: center;
                   gap: 8px;
                   background: #22c55e;
                   color: #ffffff !important;
                   font-weight: 700;
                   font-size: 14px;
                   padding: 13px 28px;
                   border-radius: 8px;
                   text-decoration: none;
                   transition: background 0.2s;
               ">
                <?php echo $is_guest ? '✅ OK' : '📋 Lihat Order Saya'; ?>
            </a>
        </div>
    </div>

    <style>
        @keyframes bcs-pulse-check {
            0% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
            }

            70% {
                box-shadow: 0 0 0 20px rgba(34, 197, 94, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        @keyframes bcs-fade-in {
            from {
                opacity: 0;
                transform: scale(0.92);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        #bcs-approved-overlay>div {
            animation: bcs-fade-in 0.4s ease forwards;
        }
    </style>

    <script>
        (function () {
            var apiUrl = <?php echo json_encode($api_url); ?>;
            var orderKey = <?php echo json_encode($order_key); ?>;
            var redirectUrl = <?php echo json_encode($redirect_url); ?>;
            var alreadyApproved = <?php echo $already_approved ? 'true' : 'false'; ?>;
            var pollInterval = 5000; // 5 detik
            var maxAttempts = 120;  // max 10 menit (120 × 5s)
            var attempts = 0;
            var overlay = document.getElementById('bcs-approved-overlay');
            var countdown = document.getElementById('bcs-countdown');
            var timer;

            function showApprovedOverlay() {
                // Tampilkan overlay
                overlay.style.display = 'flex';

                // Countdown 3 → 2 → 1 → redirect
                var count = 3;
                countdown.textContent = count;
                timer = setInterval(function () {
                    count--;
                    if (countdown) countdown.textContent = count;
                    if (count <= 0) {
                        clearInterval(timer);
                        window.location.href = redirectUrl;
                    }
                }, 1000);
            }

            // Kalau sudah approved saat page load:
            // - Guest: JANGAN tampilkan overlay (banner hijau sudah tampil di page)
            // - Logged-in: tampilkan overlay lalu redirect ke my-account/orders
            if (alreadyApproved) {
                if (<?php echo $is_guest ? 'true' : 'false'; ?>) {
                    return; // Guest: banner hijau sudah cukup, tidak perlu overlay
                }
                setTimeout(showApprovedOverlay, 500);
                return; // Tidak perlu polling
            }

            function checkStatus() {
                if (attempts >= maxAttempts) return; // stop after 10 menit
                attempts++;

                var url = apiUrl + '?key=' + encodeURIComponent(orderKey);

                fetch(url, { method: 'GET', cache: 'no-store' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && data.is_approved) {
                            // Status sudah approved → stop polling → tampilkan overlay
                            clearInterval(pollTimer);
                            showApprovedOverlay();
                        }
                    })
                    .catch(function () {
                        // Gagal fetch — diam saja, coba lagi di interval berikutnya
                    });
            }

            // Mulai polling setelah 3 detik (beri waktu page load selesai)
            var pollTimer = setInterval(checkStatus, pollInterval);
            setTimeout(checkStatus, 3000); // cek sekali duluan
        })();
    </script>
    <?php
}