<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Latest_Portfolio_Section extends Widget_Base
{
    public function get_name()
    {
        return 'latest_portfolio_section';
    }
    public function get_title()
    {
        return 'Latest Portfolio Section';
    }
    public function get_icon()
    {
        return 'eicon-gallery-grid';
    }
    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('content_section', [
            'label' => 'Latest Portfolio',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('section_title', [
            'label' => 'Section Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'Latest Portfolio',
        ]);

        $this->add_control('chat_label', [
            'label' => 'Chat Link Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'Chat BC Solutions',
        ]);

        $this->add_control('chat_url', [
            'label' => 'Chat Link URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $repeater = new Repeater();

        $repeater->add_control('image', [
            'label' => 'Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);

        $repeater->add_control('client_tag', [
            'label' => 'Client Tag',
            'type' => Controls_Manager::TEXT,
            'default' => 'Client - name',
        ]);

        $repeater->add_control('views_text', [
            'label' => 'Title Text',
            'type' => Controls_Manager::TEXT,
            'default' => '2.5 Million',
        ]);

        $repeater->add_control('client_name', [
            'label' => 'Client Name',
            'type' => Controls_Manager::TEXT,
            'default' => 'client_name',
        ]);

        $repeater->add_control('card_link', [
            'label' => 'Link URL',
            'type' => Controls_Manager::URL,
            'placeholder' => 'https://example.com',
            'default' => [
                'url' => '',
                'is_external' => true,
                'nofollow' => false,
            ],
            'show_external' => true,
        ]);

        $this->add_control('cards', [
            'label' => 'Portfolio Cards',
            'type' => Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                ['client_tag' => 'Client - bfrenza.kontraktor', 'views_text' => '2.5 Million', 'client_name' => 'bfrenzakontraktor'],
                ['client_tag' => 'Client - pasampay.id', 'views_text' => '4.8 Million', 'client_name' => 'pasampay.id'],
                ['client_tag' => 'Client - bertknowells', 'views_text' => '3.3 Million', 'client_name' => 'bertknowells'],
                ['client_tag' => 'Client - sowebazard', 'views_text' => '15.8 Million', 'client_name' => 'sowebazard'],
                ['client_tag' => 'Client - tanpa.beautiful.id', 'views_text' => '2.8 Million', 'client_name' => 'tanpa.beautiful.id'],
                ['client_tag' => 'Client - greenleaf.co', 'views_text' => '9.1 Million', 'client_name' => 'greenleaf.co'],
            ],
            'title_field' => '{{{ client_name }}}',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $title = $settings['section_title'] ?? 'Latest Portfolio';
        $chat_label = $settings['chat_label'] ?? 'Chat BC Solutions';
        $chat_url = $settings['chat_url'] ?? '#';
        $cards = $settings['cards'] ?? [];
        if (empty($cards))
            return;
        ?>

        <section class="portfolio-terbaru bg-black">
            <div class="section-header-row">
                <h2 class="text-white"><?php echo esc_html($title); ?></h2>
                <a href="<?php echo esc_url($chat_url); ?>" class="chat-link"><?php echo esc_html($chat_label); ?> <i
                        data-lucide="chevron-right"></i></a>
            </div>
            <div class="terbaru-glow"></div>
            <div class="scroll-container terbaru-scroll">
                <?php foreach ($cards as $card):
                    $img_url = !empty($card['image']['url']) ? $card['image']['url'] : '';
                    $client_tag = !empty($card['client_tag']) ? $card['client_tag'] : '';
                    $views = !empty($card['views_text']) ? $card['views_text'] : '';
                    $client = !empty($card['client_name']) ? $card['client_name'] : '';
                    $link_url = !empty($card['card_link']['url']) ? $card['card_link']['url'] : '';
                    $link_external = !empty($card['card_link']['is_external']) ? ' target="_blank"' : '';
                    $link_nofollow = !empty($card['card_link']['nofollow']) ? ' rel="nofollow"' : '';
                    $tag = $link_url ? 'a' : 'div';
                    $link_attrs = $link_url ? ' href="' . esc_url($link_url) . '"' . $link_external . $link_nofollow : '';
                    ?>
                    <<?php echo $tag; ?> class="terbaru-card"<?php echo $link_attrs; ?> style="text-decoration:none; color:inherit;">
                        <div class="terbaru-img" <?php if ($img_url): ?>data-bg="<?php echo esc_url($img_url); ?>" <?php
                        endif; ?>>
                            <div class="terbaru-client-tag"><?php echo esc_html($client_tag); ?></div>
                            <div class="terbaru-content">
                                <h3><?php echo esc_html($views); ?></h3>
                                <p><?php echo esc_html($client); ?></p>
                            </div>
                        </div>
                    </<?php echo $tag; ?>>
                <?php endforeach; ?>
            </div>
        </section>

        <script>
            if (typeof lucide !== 'undefined') lucide.createIcons();
        </script>
        <?php
    }
}
