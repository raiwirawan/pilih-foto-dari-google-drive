<?php
namespace PilihFoto;

class Admin_Menu {
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
    }

    public static function add_menu() {
        add_menu_page(
            'Pilih Foto (Studio)',
            'Pilih Foto (Studio)',
            'manage_options', // Only WP admin
            'pilih-foto',
            array( __CLASS__, 'render_page' ),
            'dashicons-images-alt2',
            30
        );
    }

    public static function render_page() {
        global $wpdb;
        $message = '';

        if ( isset($_POST['action']) && $_POST['action'] === 'create_editor' && check_admin_referer('pf_create_editor') ) {
            $username = sanitize_user($_POST['username']);
            $password = $_POST['password'];
            $name = sanitize_text_field($_POST['name']);

            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}pf_accounts WHERE username = %s", $username));
            if ($exists) {
                $message = '<div class="notice notice-error"><p>Username sudah dipakai.</p></div>';
            } else {
                $wpdb->insert(
                    "{$wpdb->prefix}pf_accounts",
                    [
                        'username' => $username,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => 'editor',
                        'display_name' => $name,
                        'created_at' => current_time('mysql', 1)
                    ]
                );
                $message = '<div class="notice notice-success"><p>Akun editor berhasil dibuat. <a href="'.esc_url(home_url('/'.PF_BASE_SLUG)).'" target="_blank">Login ke Studio</a></p></div>';
            }
        }

        $editors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pf_accounts WHERE role = 'editor'");
        $api_key = defined('PF_GOOGLE_API_KEY') ? PF_GOOGLE_API_KEY : 'Belum diatur';

        ?>
        <div class="wrap">
            <h1>Pengaturan Pilih Foto (Studio)</h1>
            <?php echo $message; ?>
            
            <p>Plugin ini menggunakan halaman mandiri <strong>/<?php echo esc_html(PF_BASE_SLUG); ?></strong> untuk login dan manajemen galeri.</p>
            
            <h2>Status API Key Google Drive</h2>
            <p><code>PF_GOOGLE_API_KEY</code> di wp-config.php: <strong><?php echo $api_key !== 'Belum diatur' ? 'Teratur (***' . substr($api_key, -4) . ')' : 'Belum diatur'; ?></strong></p>

            <h2>Daftar Editor</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Username</th><th>Nama</th><th>Dibuat</th></tr></thead>
                <tbody>
                    <?php if (empty($editors)): ?>
                        <tr><td colspan="3">Belum ada akun editor. Buat di bawah.</td></tr>
                    <?php else: ?>
                        <?php foreach($editors as $ed): ?>
                            <tr>
                                <td><?php echo esc_html($ed->username); ?></td>
                                <td><?php echo esc_html($ed->display_name); ?></td>
                                <td><?php echo esc_html($ed->created_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2>Buat Akun Editor Baru</h2>
            <form method="post">
                <?php wp_nonce_field('pf_create_editor'); ?>
                <input type="hidden" name="action" value="create_editor">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="username">Username</label></th>
                        <td><input name="username" type="text" id="username" value="" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="name">Nama Lengkap</label></th>
                        <td><input name="name" type="text" id="name" value="" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="password">Password</label></th>
                        <td><input name="password" type="password" id="password" value="" class="regular-text" required></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Buat Editor">
                </p>
            </form>
        </div>
        <?php
    }
}
