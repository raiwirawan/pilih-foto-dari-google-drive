<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Portfolio_Hero_Section extends Widget_Base
{
    public function get_name()
    {
        return 'portfolio_hero_section';
    }
    public function get_title()
    {
        return 'Portfolio Hero';
    }
    public function get_icon()
    {
        return 'eicon-banner';
    }
    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        // BACKGROUND
        $this->start_controls_section('bg_section', [
            'label' => 'Background',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('hero_bg', [
            'label' => 'Background Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);
        $this->end_controls_section();

        // CONTENT
        $this->start_controls_section('content_section', [
            'label' => 'Content',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('label', [
            'label' => 'Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC SOLUTIONS PORTFOLIO',
        ]);
        $this->add_control('brand', [
            'label' => 'Brand Name',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC SOLUTIONS',
        ]);
        $this->add_control('title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'Visual Contents Portfolio',
        ]);
        $this->add_control('synopsis', [
            'label' => 'Synopsis',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'Showcasing our creative digital marketing work � from social media management and brand activation to professional photography and visual storytelling.',
        ]);
        $this->end_controls_section();

        // CREDITS
        $this->start_controls_section('credits_section', [
            'label' => 'Credits',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        $repeater = new Repeater();
        $repeater->add_control('credit_label', [
            'label' => 'Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'Production',
        ]);
        $repeater->add_control('credit_value', [
            'label' => 'Value',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC Solutions Creative Agency',
        ]);
        $this->add_control('credits', [
            'label' => 'Credits',
            'type' => Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                ['credit_label' => 'Production', 'credit_value' => 'BC Solutions Creative Agency'],
                ['credit_label' => 'Services', 'credit_value' => 'Visual Contents � Brand Activation'],
            ],
            'title_field' => '{{{ credit_label }}}',
        ]);
        $this->end_controls_section();

        // TAGS
        $this->start_controls_section('tags_section', [
            'label' => 'Tags',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        $tag_repeater = new Repeater();
        $tag_repeater->add_control('tag_text', [
            'label' => 'Tag Text',
            'type' => Controls_Manager::TEXT,
            'default' => 'Tag',
        ]);
        $tag_repeater->add_control('is_highlight', [
            'label' => 'Highlight',
            'type' => Controls_Manager::SWITCHER,
            'default' => '',
        ]);
        $this->add_control('tags', [
            'label' => 'Tags',
            'type' => Controls_Manager::REPEATER,
            'fields' => $tag_repeater->get_controls(),
            'default' => [
                ['tag_text' => 'Visual Contents', 'is_highlight' => ''],
                ['tag_text' => 'Photography', 'is_highlight' => ''],
                ['tag_text' => '2025', 'is_highlight' => ''],
                ['tag_text' => 'BC Solutions Portfolio', 'is_highlight' => 'yes'],
            ],
            'title_field' => '{{{ tag_text }}}',
        ]);
        $this->end_controls_section();

        // BUTTONS
        $this->start_controls_section('buttons_section', [
            'label' => 'Buttons',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('btn1_label', [
            'label' => 'Primary Button Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'Watch Now',
        ]);
        $this->add_control('btn1_url', [
            'label' => 'Primary Button URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $this->add_control('btn2_label', [
            'label' => 'Secondary Button Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'Coming Soon',
        ]);
        $this->add_control('btn2_url', [
            'label' => 'Secondary Button URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);
        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $bg  = !empty($s['hero_bg']['url']) ? $s['hero_bg']['url'] : '';
        $url = esc_url($s['btn1_url']);
        // Auto-detect: if btn1_url points to an image file, show popup instead of opening link
        $is_img = !empty($url) && preg_match('/\.(webp|jpe?g|png|gif|avif)(\?.*)?$/i', $url);
        $pid    = 'ph-popup-' . esc_attr($this->get_id());
        ?>
        <?php if ($is_img): ?>
        <link rel="preload" as="image" href="<?php echo $url; ?>">
        <?php endif; ?>
        <section class="film-hero">
            <?php if ($bg): ?>
                <div class="film-hero-bg" style="background-image: url('<?php echo esc_url($bg); ?>');"></div>
            <?php endif; ?>
            <div class="film-hero-overlay"></div>
            <div class="film-hero-content">
                <span class="film-label"><?php echo esc_html($s['label']); ?></span>
                <p class="film-brand"><?php echo esc_html($s['brand']); ?></p>
                <h1 class="film-title-handwritten"><?php echo nl2br(esc_html($s['title'])); ?></h1>
                <p class="film-synopsis"><?php echo esc_html($s['synopsis']); ?></p>

                <?php if (!empty($s['credits'])): ?>
                    <div class="film-credits">
                        <?php foreach ($s['credits'] as $c): ?>
                            <p><em><?php echo esc_html($c['credit_label']); ?>:</em>
                                <strong><?php echo esc_html($c['credit_value']); ?></strong></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($s['tags'])): ?>
                    <div class="film-tags">
                        <?php foreach ($s['tags'] as $i => $tag):
                            if ($i > 0): ?><span class="film-tag-sep">&middot;</span><?php endif;
                            $cls = ($tag['is_highlight'] === 'yes') ? 'film-tag film-tag-highlight' : 'film-tag'; ?>
                            <span class="<?php echo $cls; ?>"><?php echo esc_html($tag['tag_text']); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="film-hero-buttons">
                    <?php if (!empty($s['btn1_label'])): ?>
                        <?php if ($is_img): ?>
                            <button type="button" class="btn-film-primary"
                                onclick="document.getElementById('<?php echo $pid; ?>').style.display='flex';document.body.style.overflow='hidden'">
                                <i data-lucide="image"></i> <?php echo esc_html($s['btn1_label']); ?>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo $url; ?>" class="btn-film-primary" target="_blank" rel="noopener">
                                <i data-lucide="play-circle"></i> <?php echo esc_html($s['btn1_label']); ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!empty($s['btn2_label'])): ?>
                        <a href="<?php echo esc_url($s['btn2_url']); ?>" class="btn-film-outline" target="_blank" rel="noopener">
                            <i data-lucide="clock"></i> <?php echo esc_html($s['btn2_label']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if ($is_img): ?>
        <!-- Image Popup — fully self-contained, no external JS/CSS needed -->
        <div id="<?php echo $pid; ?>"
             style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.93);align-items:center;justify-content:center;cursor:pointer;"
             onclick="if(event.target.id==='<?php echo $pid; ?>')this.style.display='none',document.body.style.overflow=''">
            <button onclick="document.getElementById('<?php echo $pid; ?>').style.display='none';document.body.style.overflow=''"
                    style="position:absolute;top:20px;right:24px;width:44px;height:44px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:50%;color:#fff;font-size:1.5rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                &times;
            </button>
            <!-- img src here = browser preloads image even while popup is hidden -->
            <img src="<?php echo $url; ?>" alt="Price List"
                 style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;box-shadow:0 30px 80px rgba(0,0,0,.8);cursor:default;">
        </div>
        <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var p = document.getElementById('<?php echo $pid; ?>');
                if (p && p.style.display !== 'none') { p.style.display = 'none'; document.body.style.overflow = ''; }
            }
        });
        </script>
        <?php endif; ?>

        <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
        <?php
    }
}
