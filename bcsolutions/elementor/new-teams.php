<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Team_Section extends Widget_Base
{
    public function get_name()
    {
        return 'team_section';
    }
    public function get_title()
    {
        return 'Team Section';
    }
    public function get_icon()
    {
        return 'eicon-person';
    }
    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('team_section', [
            'label' => 'Team',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('team_title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC Solutions Team',
        ]);

        $team = new Repeater();

        $team->add_control('media_type', [
            'label' => 'Media Type',
            'type' => Controls_Manager::SELECT,
            'default' => 'image',
            'options' => [
                'image' => 'Image',
                'video' => 'Video',
            ],
        ]);

        $team->add_control('image', [
            'label' => 'Photo',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
            'condition' => ['media_type' => 'image'],
        ]);

        $team->add_control('video', [
            'label' => 'Video',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['video'],
            'condition' => ['media_type' => 'video'],
        ]);

        $team->add_control('overlay_label', [
            'label' => 'Overlay Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC SOLUTIONS PICTURES',
        ]);

        $team->add_control('name', [
            'label' => 'Name / Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC Solutions Production',
        ]);

        $team->add_control('subtitle', [
            'label' => 'Subtitle',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC Solutions - Pictures',
        ]);

        $this->add_control('team_members', [
            'label' => 'Team Cards',
            'type' => Controls_Manager::REPEATER,
            'fields' => $team->get_controls(),
            'default' => [
                ['name' => 'BC Solutions Production', 'subtitle' => 'BC Solutions - Pictures', 'overlay_label' => 'BC SOLUTIONS PICTURES'],
                ['name' => 'BC Solutions Production', 'subtitle' => 'BC Solutions - Short Movie', 'overlay_label' => 'BC SOLUTIONS PICTURES'],
                ['name' => 'BC Solutions Crew', 'subtitle' => 'BC Solutions - Pictures', 'overlay_label' => 'BC SOLUTIONS PICTURES'],
                ['name' => 'Production Team', 'subtitle' => 'BC Solutions - Sinefort', 'overlay_label' => 'BC SOLUTIONS PICTURES'],
                ['name' => 'BC Solutions Crew', 'subtitle' => 'BC Solutions - Short Movie', 'overlay_label' => 'BC SOLUTIONS PICTURES'],
            ],
            'title_field' => '{{{ name }}}',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $title = $s['team_title'] ?? 'BC Solutions Team';
        $members = $s['team_members'] ?? [];
        ?>

        <section class="services bg-black">
            <div class="section-header">
                <h2 class="text-white"><?php echo esc_html($title); ?></h2>
            </div>

            <div class="swiper-container-wrapper">
                <button class="slider-nav-btn prev-btn swiper-prev-team"><i data-lucide="chevron-left"></i></button>
                <button class="slider-nav-btn next-btn swiper-next-team"><i data-lucide="chevron-right"></i></button>

                <div class="stacked-scroll">
                    <?php foreach ($members as $m):
                        $media_type = $m['media_type'] ?? 'image';
                        $img = !empty($m['image']['url']) ? esc_url($m['image']['url']) : '';
                        $video = !empty($m['video']['url']) ? esc_url($m['video']['url']) : '';
                        $label = esc_html($m['overlay_label'] ?? '');
                        $name = esc_html($m['name'] ?? '');
                        $sub = esc_html($m['subtitle'] ?? '');
                        ?>
                        <div class="stacked-card port-card portrait">
                            <div class="port-img" <?php if ($media_type === 'image' && $img): ?>data-bg="<?php echo $img; ?>" <?php endif; ?>>
                                <?php if ($media_type === 'video' && $video): ?>
                                    <video data-lazy-video muted loop playsinline preload="none"
                                        style="width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0; border-radius:16px;">
                                        <source data-src="<?php echo $video; ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                                <?php if ($label): ?>
                                    <div class="team-overlay"><?php echo $label; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="port-stats-outer">
                                <h4><?php echo $name; ?></h4>
                                <p class="text-gray-sm mt-1"><?php echo $sub; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <script>
            if (typeof lucide !== 'undefined') lucide.createIcons();
        </script>
        <?php
    }
}
