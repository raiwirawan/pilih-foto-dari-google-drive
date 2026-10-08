<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Running_Text extends Widget_Base {

    public function get_name() {
        return 'running_text';
    }

    public function get_title() {
        return 'Running Text';
    }

    public function get_icon() {
        return 'eicon-animation-text';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Running Text',
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        // Repeater
        $repeater = new Repeater();

        $repeater->add_control(
            'text',
            [
                'label'   => 'Text',
                'type'    => Controls_Manager::TEXT,
                'default' => 'This is running text',
            ]
        );

        $this->add_control(
            'items',
            [
                'label'   => 'Texts',
                'type'    => Controls_Manager::REPEATER,
                'fields'  => $repeater->get_controls(),
                'default' => [
                    ['text' => 'Welcome to our website'],
                    ['text' => 'Special promo available now'],
                ],
                'title_field' => '{{{ text }}}',
            ]
        );

        $this->add_control(
            'speed',
            [
                'label'   => 'Speed (seconds)',
                'type'    => Controls_Manager::NUMBER,
                'default' => 20,
                'min'     => 5,
                'max'     => 100,
            ]
        );

        $this->add_control(
            'direction',
            [
                'label'   => 'Direction',
                'type'    => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'left'  => 'Left to Right',
                    'right' => 'Right to Left',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if (empty($settings['items'])) return;

        $direction = $settings['direction'] === 'right' ? 'reverse' : 'normal';
        ?>

        <div class="running-text-wrapper">
            <div class="running-text-track"
                 style="animation-duration: <?php echo esc_attr($settings['speed']); ?>s;
                        animation-direction: <?php echo esc_attr($direction); ?>;">
                <?php foreach ($settings['items'] as $item): ?>
                    <span class="running-text-item">
                        <?php echo esc_html($item['text']); ?>
                    </span>
                <?php endforeach; ?>

                <!-- Duplicate for seamless loop -->
                <?php foreach ($settings['items'] as $item): ?>
                    <span class="running-text-item">
                        <?php echo esc_html($item['text']); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
    }
}
