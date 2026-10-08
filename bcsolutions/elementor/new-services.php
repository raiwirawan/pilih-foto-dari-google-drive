<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Services_Section extends Widget_Base
{
    public function get_name()
    {
        return 'services_section';
    }
    public function get_title()
    {
        return 'Services Section';
    }
    public function get_icon()
    {
        return 'eicon-apps';
    }
    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('services_section', [
            'label' => 'Services',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('services_title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC Solutions Services',
        ]);

        $svc = new Repeater();

        $svc->add_control('title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'Service Name',
        ]);

        $svc->add_control('category', [
            'label' => 'Category',
            'type' => Controls_Manager::TEXT,
            'default' => 'Category',
        ]);

        $svc->add_control('image', [
            'label' => 'Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);

        $svc->add_control('bg_color', [
            'label' => 'Background Color',
            'type' => Controls_Manager::SELECT,
            'default' => 'bg-red',
            'options' => [
                'bg-red' => 'Red',
                'bg-red-light' => 'Red Light',
                'bg-red-dark' => 'Red Dark',
                'bg-white' => 'White',
                'bg-black' => 'Black',
                'bg-black-light' => 'Black Light',
            ],
        ]);

        $svc->add_control('url', [
            'label' => 'Link URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $this->add_control('services', [
            'label' => 'Service Cards',
            'type' => Controls_Manager::REPEATER,
            'fields' => $svc->get_controls(),
            'default' => [
                ['title' => 'Social Media Management', 'category' => 'Marketing', 'bg_color' => 'bg-red'],
                ['title' => 'Pro Photography 360', 'category' => 'Visual', 'bg_color' => 'bg-white'],
                ['title' => 'Visual Contents', 'category' => 'Creative', 'bg_color' => 'bg-red-light'],
                ['title' => 'Brand Activation', 'category' => 'Strategy', 'bg_color' => 'bg-white'],
                ['title' => 'Paid Ads', 'category' => 'Advertising', 'bg_color' => 'bg-red-dark'],
                ['title' => 'Website Development', 'category' => 'Digital', 'bg_color' => 'bg-white'],
                ['title' => 'SEO Optimization', 'category' => 'Growth', 'bg_color' => 'bg-red'],
            ],
            'title_field' => '{{{ title }}}',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $title = $s['services_title'] ?? 'BC Solutions Services';
        $services = $s['services'] ?? [];
        if (empty($services))
            return;
        ?>

        <section class="services bg-black">
            <div class="section-header">
                <h2 class="text-white"><?php echo esc_html($title); ?></h2>
            </div>

            <div class="swiper-container-wrapper">
                <button class="slider-nav-btn prev-btn swiper-prev-services"><i data-lucide="chevron-left"></i></button>
                <button class="slider-nav-btn next-btn swiper-next-services"><i data-lucide="chevron-right"></i></button>

                <div class="swiper swiper-services">
                    <div class="swiper-wrapper">
                        <?php foreach ($services as $svc):
                            $bg = esc_attr($svc['bg_color'] ?? 'bg-red');
                            $t = esc_html($svc['title'] ?? '');
                            $cat = esc_html($svc['category'] ?? '');
                            $img = !empty($svc['image']['url']) ? esc_url($svc['image']['url']) : '';
                            $img_id = !empty($svc['image']['id']) ? $svc['image']['id'] : 0;
                            $url = !empty($svc['url']) ? esc_url($svc['url']) : '#';
                            ?>
                            <div class="swiper-slide">
                                <a href="<?php echo $url; ?>" class="layanan-card <?php echo $bg; ?>">
                                    <div class="layanan-text">
                                        <h4><?php echo $t; ?></h4>
                                        <span><?php echo $cat; ?></span>
                                    </div>
                                    <?php if ($img_id && function_exists('bcs_img_tag')): ?>
                                        <?php echo bcs_img_tag($img_id, 'medium', 'layanan-img', $t, true); ?>
                                    <?php elseif ($img): ?>
                                        <img src="<?php echo $img; ?>" alt="<?php echo $t; ?>" class="layanan-img" loading="lazy" decoding="async">
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <script>
            if (typeof lucide !== 'undefined') lucide.createIcons();
        </script>
        <?php
    }
}
