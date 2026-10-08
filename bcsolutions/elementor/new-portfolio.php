<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Portfolio_Section extends Widget_Base
{
    public function get_name()
    {
        return 'portfolio_section';
    }
    public function get_title()
    {
        return 'Portfolio Section';
    }
    public function get_icon()
    {
        return 'eicon-posts-grid';
    }
    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('content_section', [
            'label' => 'Portfolio Cards',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('section_title', [
            'label' => 'Section Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC Solutions Portfolio',
        ]);

        $repeater = new Repeater();

        $repeater->add_control('image', [
            'label' => 'Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);

        $repeater->add_control('tint_color', [
            'label' => 'Tint Style',
            'type' => Controls_Manager::SELECT,
            'default' => 'bg-red-tint',
            'options' => [
                'bg-red-tint' => 'Red Tint',
                'bg-black-tint' => 'Black Tint',
            ],
        ]);

        $repeater->add_control('platform', [
            'label' => 'Platform',
            'type' => Controls_Manager::SELECT,
            'default' => 'reels',
            'options' => [
                'reels' => 'Instagram Reels',
                'tiktok' => 'TikTok',
            ],
        ]);

        $repeater->add_control('views_text', [
            'label' => 'Views Text',
            'type' => Controls_Manager::TEXT,
            'default' => '1.4 Million',
        ]);

        $repeater->add_control('client_name', [
            'label' => 'Client Name',
            'type' => Controls_Manager::TEXT,
            'default' => 'client_name',
        ]);

        $repeater->add_control('video_url', [
            'label' => 'Video Link (Reels/TikTok)',
            'type' => Controls_Manager::URL,
            'placeholder' => 'https://...',
            'default' => [
                'url' => '',
                'is_external' => true,
                'nofollow' => true,
                'custom_attributes' => '',
            ],
        ]);

        $repeater->add_control('hover_video', [
            'label' => 'Hover Video (MP4)',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['video'],
        ]);

        $this->add_control('cards', [
            'label' => 'Portfolio Cards',
            'type' => Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                ['platform' => 'reels', 'views_text' => '1.4 Million', 'client_name' => 'jelitareload_official', 'tint_color' => 'bg-red-tint'],
                ['platform' => 'reels', 'views_text' => '5.4 Million', 'client_name' => 'ndbabyshop_nanda', 'tint_color' => 'bg-black-tint'],
                ['platform' => 'tiktok', 'views_text' => '5.8 Million', 'client_name' => 'vitagerdofficial', 'tint_color' => 'bg-red-tint'],
                ['platform' => 'tiktok', 'views_text' => '7 Million', 'client_name' => 'Bumbu_mazzoni', 'tint_color' => 'bg-black-tint'],
                ['platform' => 'reels', 'views_text' => '3.2 Million', 'client_name' => 'Pasmira Official', 'tint_color' => 'bg-red-tint'],
                ['platform' => 'reels', 'views_text' => '6.8 Million', 'client_name' => 'With Me', 'tint_color' => 'bg-black-tint'],
            ],
            'title_field' => '{{{ client_name }}}',
        ]);

        $this->end_controls_section();
    }

    private function render_instagram_svg()
    {
        return '<svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85C2.38 3.86 3.9 2.31 7.15 2.23 8.42 2.17 8.8 2.16 12 2.16zM12 0C8.74 0 8.33.01 7.05.07 2.7.27.27 2.7.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.2 4.36 2.62 6.78 6.98 6.98C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c4.35-.2 6.78-2.62 6.98-6.98.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.2-4.35-2.62-6.78-6.98-6.98C15.67.01 15.26 0 12 0zm0 5.84A6.16 6.16 0 1 0 18.16 12 6.16 6.16 0 0 0 12 5.84zM12 16a4 4 0 1 1 4-4 4 4 0 0 1-4 4zm6.41-11.85a1.44 1.44 0 1 0 1.44 1.44 1.44 1.44 0 0 0-1.44-1.44z"/></svg>';
    }

    private function render_tiktok_svg()
    {
        return '<svg viewBox="0 0 48 48" width="20" height="20" fill="white"><path d="M38.4 21.68V16c-2.66 0-4.69-.71-6.04-2.11-1.46-1.53-2.23-3.69-2.3-6.44V6.84h-5.53v22.8c0 2.27-1.3 4.35-3.35 5.33a5.97 5.97 0 0 1-6.36-.72 5.87 5.87 0 0 1 .72-9.54 5.97 5.97 0 0 1 3.36-1.03V18.1a11.47 11.47 0 0 0-7.52 3.2 11.34 11.34 0 0 0 .63 16.6A11.47 11.47 0 0 0 24.5 37.3a11.34 11.34 0 0 0 3.55-8.22V18.44A16.07 16.07 0 0 0 38.4 21.68z"/></svg>';
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $title = $settings['section_title'] ?? 'BC Solutions Portfolio';
        $cards = $settings['cards'] ?? [];
        if (empty($cards))
            return;
?>

        <section class="portfolio bg-black pt-sm">
            <div class="section-header">
                <h2 class="text-white"><?php echo esc_html($title); ?></h2>
            </div>

            <div class="swiper-container-wrapper">
                <button class="slider-nav-btn prev-btn swiper-prev-reels"><i data-lucide="chevron-left"></i></button>
                <button class="slider-nav-btn next-btn swiper-next-reels"><i data-lucide="chevron-right"></i></button>

                <div class="stacked-scroll">
                    <?php foreach ($cards as $card):
            $img_url = !empty($card['image']['url']) ? $card['image']['url'] : '';
            $tint = !empty($card['tint_color']) ? $card['tint_color'] : 'bg-red-tint';
            $platform = !empty($card['platform']) ? $card['platform'] : 'reels';
            $views = !empty($card['views_text']) ? $card['views_text'] : '';
            $client = !empty($card['client_name']) ? $card['client_name'] : '';
            
            // Video URL
            $video_url = !empty($card['video_url']['url']) ? $card['video_url']['url'] : '';
            $target = !empty($card['video_url']['is_external']) ? '_blank' : '_self';
            $nofollow = !empty($card['video_url']['nofollow']) ? 'rel="nofollow"' : '';
            
            // Hover Video
            $hover_video_url = !empty($card['hover_video']['url']) ? $card['hover_video']['url'] : '';
            
            $platform_label = ($platform === 'tiktok') ? 'TikTok' : 'Reels';
            $platform_svg = ($platform === 'tiktok') ? $this->render_tiktok_svg() : $this->render_instagram_svg();
?>
                        <div class="stacked-card port-card portrait">
                            <?php if ($video_url): ?>
                            <a href="<?php echo esc_url($video_url); ?>" target="<?php echo esc_attr($target); ?>" <?php echo $nofollow; ?> class="port-card-link">
                            <?php endif; ?>
                            
                            <div class="port-img <?php echo esc_attr($tint); ?>"
                                <?php if ($img_url): ?>data-bg="<?php echo esc_url($img_url); ?>"<?php
            endif; ?>>
                                
                                <?php if ($hover_video_url): ?>
                                <video class="port-hover-video" src="<?php echo esc_url($hover_video_url); ?>" muted loop playsinline preload="metadata"></video>
                                <?php endif; ?>

                                <div class="platform-badge"><?php echo $platform_svg; ?> <span><?php echo esc_html($platform_label); ?></span></div>
                                <div class="port-img-stats">
                                    <!-- <h3><?php echo esc_html($views); ?><br>Views!</h3> -->
                                    <p>Client - <?php echo esc_html($client); ?></p>
                                </div>
                            </div>
                            
                            <?php if ($video_url): ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php
        endforeach; ?>
                </div>
            </div>
        </section>

        <script>
            if (typeof lucide !== 'undefined') lucide.createIcons();
        </script>
        <?php
    }
}