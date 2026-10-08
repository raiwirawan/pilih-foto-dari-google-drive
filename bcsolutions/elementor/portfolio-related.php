<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Portfolio_Related_Section extends Widget_Base
{
    public function get_name() { return 'portfolio_related_section'; }
    public function get_title() { return 'Portfolio Related'; }
    public function get_icon() { return 'eicon-posts-carousel'; }
    public function get_categories() { return ['general']; }

    protected function register_controls()
    {
        $this->start_controls_section('related_section', [
            'label' => 'Related Content',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('heading', [
            'label' => 'Heading',
            'type' => Controls_Manager::TEXT,
            'default' => 'More from BC Solutions',
        ]);

        $repeater = new Repeater();
        $repeater->add_control('card_image', [
            'label' => 'Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);
        $repeater->add_control('card_title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'Service Name',
        ]);
        $repeater->add_control('card_desc', [
            'label' => 'Description',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'Brief description of this service',
        ]);
        $repeater->add_control('card_label', [
            'label' => 'Card Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'Service',
        ]);
        $repeater->add_control('card_url', [
            'label' => 'Link URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $this->add_control('cards', [
            'label' => 'Cards',
            'type' => Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                ['card_title' => 'Social Media Management', 'card_desc' => 'Comprehensive social media strategy and content creation for brand growth', 'card_label' => 'SMM Campaign'],
                ['card_title' => 'Pro Photography 360�', 'card_desc' => 'Professional 360-degree photography for immersive visual experiences', 'card_label' => 'Photo 360�'],
                ['card_title' => 'Brand Activation', 'card_desc' => 'Creating memorable brand experiences that engage and inspire audiences', 'card_label' => 'Brand Activation'],
                ['card_title' => 'Visual Contents', 'card_desc' => 'High-quality visual content production for digital marketing campaigns', 'card_label' => 'Visual Contents'],
                ['card_title' => 'Paid Ads Campaign', 'card_desc' => 'Data-driven paid advertising on Google, Meta & TikTok platforms', 'card_label' => 'Paid Ads'],
                ['card_title' => 'SEO & Website', 'card_desc' => 'Building optimized websites with strong search engine presence', 'card_label' => 'SEO & Web'],
            ],
            'title_field' => '{{{ card_title }}}',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $cards = $s['cards'] ?? [];
        ?>
        <div class="film-related">
            <h3 class="film-related-heading"><?php echo esc_html($s['heading']); ?></h3>
            <div class="related-cards-wrapper">
                <button class="slider-nav-btn prev-btn btn-prev-related"><i data-lucide="chevron-left"></i></button>
                <button class="slider-nav-btn next-btn btn-next-related"><i data-lucide="chevron-right"></i></button>
                <div class="related-cards-track">
                    <?php foreach ($cards as $card):
                        $img = !empty($card['card_image']['url']) ? $card['card_image']['url'] : '';
                        $url = !empty($card['card_url']) ? $card['card_url'] : '#';
                    ?>
                    <div class="related-card">
                        <a href="<?php echo esc_url($url); ?>" class="related-card-inner">
                            <?php if ($img): ?>
                            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($card['card_title']); ?>">
                            <?php endif; ?>
                            <div class="related-card-text">
                                <h4><?php echo esc_html($card['card_title']); ?></h4>
                                <p><?php echo esc_html($card['card_desc']); ?></p>
                            </div>
                        </a>
                        <p class="related-card-title"><?php echo esc_html($card['card_label']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
        (function(){
            var track = document.querySelector('.related-cards-track');
            var bp = document.querySelector('.btn-prev-related');
            var bn = document.querySelector('.btn-next-related');
            if (track && bp && bn) {
                bn.addEventListener('click', function(){ track.scrollBy({left:200,behavior:'smooth'}); });
                bp.addEventListener('click', function(){ track.scrollBy({left:-200,behavior:'smooth'}); });
            }
        })();
        </script>
        <?php
    }
}
