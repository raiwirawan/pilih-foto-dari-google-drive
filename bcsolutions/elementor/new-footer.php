<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Footer_Section extends Widget_Base
{
    public function get_name() { return 'footer_section'; }
    public function get_title() { return 'Footer Section'; }
    public function get_icon() { return 'eicon-footer'; }
    public function get_categories() { return ['general']; }

    protected function register_controls()
    {
        // ========================
        // BRAND INFO (Column 1)
        // ========================
        $this->start_controls_section('brand_section', [
            'label' => 'Brand Logo',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('brand_logo', [
            'label' => 'Logo',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
            'description' => 'Upload logo (will display at 180px width)',
        ]);

        $this->add_control('brand_desc', [
            'label' => 'Description',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'A full-service creative agency specializing in social media management, visual content production, and digital marketing solutions for brands across diverse industries.',
        ]);

        $this->end_controls_section();

        // ========================
        // OUR SERVICES (Column 2)
        // ========================
        $this->start_controls_section('services_col', [
            'label' => 'Our Services',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('services_title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'OUR SERVICES',
        ]);

        $svc = new Repeater();
        $svc->add_control('label', [
            'label' => 'Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'Service',
        ]);
        $svc->add_control('url', [
            'label' => 'URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $this->add_control('services_links', [
            'label' => 'Links',
            'type' => Controls_Manager::REPEATER,
            'fields' => $svc->get_controls(),
            'default' => [
                ['label' => 'Social Media Management', 'url' => '#'],
                ['label' => 'Pro Photography - 360', 'url' => '#'],
                ['label' => 'Visual Contents', 'url' => '#'],
                ['label' => 'Brand Activation', 'url' => '#'],
            ],
            'title_field' => '{{{ label }}}',
        ]);

        $this->end_controls_section();

        // ========================
        // STUDIO (Column 3)
        // ========================
        $this->start_controls_section('studio_col', [
            'label' => 'Studio Info',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('studio_title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC SOLUTIONS STUDIO',
        ]);

        $this->add_control('studio_address', [
            'label' => 'Address',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'Jl. Gunung Salak Utara gg papandayan, Padangsambian Klod, Kec. Denpasar Bar., Kota Denpasar, Bali 80119',
        ]);

        $this->add_control('studio_desc', [
            'label' => 'Extra Description',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'BC Solutions has handled various clients from almost every province in Indonesia across diverse business industries.',
        ]);

        $this->end_controls_section();

        // ========================
        // SOCIAL MEDIA (Column 4)
        // ========================
        $this->start_controls_section('social_col', [
            'label' => 'Social Media',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('social_title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'OUR SOCIAL MEDIA',
        ]);

        $social = new Repeater();
        $social->add_control('label', [
            'label' => 'Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'Instagram',
        ]);
        $social->add_control('url', [
            'label' => 'URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $this->add_control('social_links', [
            'label' => 'Links',
            'type' => Controls_Manager::REPEATER,
            'fields' => $social->get_controls(),
            'default' => [
                ['label' => 'WhatsApp', 'url' => '#'],
                ['label' => 'Instagram', 'url' => '#'],
                ['label' => 'TikTok', 'url' => '#'],
                ['label' => 'Email', 'url' => '#'],
            ],
            'title_field' => '{{{ label }}}',
        ]);

        $this->end_controls_section();

        // ========================
        // CONTACT (Column 5)
        // ========================
        $this->start_controls_section('contact_col', [
            'label' => 'Contact',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('contact_title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'CONTACT BC SOLUTIONS',
        ]);

        $this->add_control('contact_text', [
            'label' => 'Contact Info',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'WhatsApp: 081234567890',
        ]);

        $this->end_controls_section();

        // ========================
        // BOTTOM BAR
        // ========================
        $this->start_controls_section('bottom_section', [
            'label' => 'Footer Bottom',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('copyright', [
            'label' => 'Copyright Text',
            'type' => Controls_Manager::TEXT,
            'default' => 'Copyright &copy; 2026 BC Solutions. All rights reserved.',
        ]);

        $this->add_control('privacy_text', [
            'label' => 'Privacy Policy Text',
            'type' => Controls_Manager::TEXT,
            'default' => 'Privacy Policy',
        ]);

        $this->add_control('privacy_url', [
            'label' => 'Privacy Policy URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $this->add_control('terms_text', [
            'label' => 'Terms Text',
            'type' => Controls_Manager::TEXT,
            'default' => 'Terms of Service',
        ]);

        $this->add_control('terms_url', [
            'label' => 'Terms URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        ?>

        <footer class="footer">
            <div class="footer-grid">
                <!-- Brand Logo -->
                <div class="footer-col col-span-2">
                    <?php if (!empty($s['brand_logo']['url'])): ?>
                        <img src="<?php echo esc_url($s['brand_logo']['url']); ?>" alt="Logo" class="brand_logo">
                    <?php else: ?>
                        <h2>BC SOLUTIONS</h2>
                    <?php endif; ?>
                    <?php if (!empty($s['brand_desc'])): ?>
                        <p><?php echo esc_html($s['brand_desc']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Services -->
                <div class="footer-col">
                    <h4><?php echo esc_html($s['services_title']); ?></h4>
                    <?php if (!empty($s['services_links'])): ?>
                    <ul>
                        <?php foreach ($s['services_links'] as $link): ?>
                            <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <!-- Studio -->
                <div class="footer-col">
                    <h4><?php echo esc_html($s['studio_title']); ?></h4>
                    <p><?php echo esc_html($s['studio_address']); ?></p>
                    <?php if (!empty($s['studio_desc'])): ?>
                        <p class="mt-4"><?php echo esc_html($s['studio_desc']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Social Media -->
                <div class="footer-col">
                    <h4><?php echo esc_html($s['social_title']); ?></h4>
                    <?php if (!empty($s['social_links'])): ?>
                    <ul>
                        <?php foreach ($s['social_links'] as $link): ?>
                            <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <!-- Contact -->
                <div class="footer-col">
                    <h4><?php echo esc_html($s['contact_title']); ?></h4>
                    <p><?php echo nl2br(esc_html($s['contact_text'])); ?></p>
                </div>
            </div>

            <div class="footer-bottom">
                <p><?php echo $s['copyright']; ?></p>
                <div class="footer-legal">
                    <a href="<?php echo esc_url($s['privacy_url']); ?>"><?php echo esc_html($s['privacy_text']); ?></a>
                    <a href="<?php echo esc_url($s['terms_url']); ?>"><?php echo esc_html($s['terms_text']); ?></a>
                </div>
                <button class="back-to-top"><i data-lucide="arrow-up"></i></button>
            </div>
        </footer>

        <script>
            if (typeof lucide !== 'undefined') lucide.createIcons();
        </script>
        <?php
    }
}
