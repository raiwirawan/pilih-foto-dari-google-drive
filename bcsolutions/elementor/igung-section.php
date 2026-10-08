<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Tabanan_Tourism_Widget extends Widget_Base
{

    public function get_name()
    {
        return 'tabanan_tourism_ai';
    }

    public function get_title()
    {
        return 'Tabanan Tourism AI';
    }

    public function get_icon()
    {
        return 'eicon-map-pin';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => 'AI Prompt',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'default_prompt',
            [
                'label' => 'Default Prompt',
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Please give me recommendation of Tabanan tourism',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $uid = $this->get_id();
        ?>

        <div class="tabanan-ai-wrapper" id="tabanan-<?php echo esc_attr($uid); ?>">

            <div class="tabanan-header">
                <h1 id="typing-title" data-text="Halo saya I Gung !">Halo saya I Gung ! </h1>
                <p>Ada yang bisa saya bantu?</p>
            </div>

            <div class="tabanan-prompt-box">
                <textarea class="tabanan-prompt"><?php echo esc_textarea($settings['default_prompt']); ?></textarea>
                <button class="tabanan-btn">Get Recommendation</button>
                <div class="tabanan-loader" style="display:none;">Loading...</div>
            </div>
            <img class="igung_charcater" src="https://phritabanan.com/wp-content/uploads/2025/12/igung-1.png"></img>
            <div class="tabanan-results"></div>
        </div>

        <script>
            function typeText(element, text, speed = 50) {
                if (!element) return;

                element.innerHTML = '';
                let index = 0;

                const typing = setInterval(() => {
                    element.innerHTML += text.charAt(index);
                    index++;

                    if (index >= text.length) {
                        clearInterval(typing);
                    }
                }, speed);
            }
            (function () {



                const wrapper = document.querySelector('#tabanan-<?php echo esc_js($uid); ?>');
                if (!wrapper) return;
                const titleEl = wrapper.querySelector('#typing-title');
                if (titleEl) {
                    const text = titleEl.getAttribute('data-text');
                    typeText(titleEl, text, 80);
                }
                const btn = wrapper.querySelector('.tabanan-btn');
                const textarea = wrapper.querySelector('.tabanan-prompt');
                const results = wrapper.querySelector('.tabanan-results');
                const loader = wrapper.querySelector('.tabanan-loader');
                const systemPrompt = `
You are a an AI named I Gung that created by PHRI Tabanan.
Rules:
- Answer clearly and factually.
- Always send accomodation recomendation names including ('bali jugnle camping, green oasis')
- If the user asks about Tabanan, tourism, hotels, restaurants, hospitality businesses, or digital solutions,
  naturally recommend this selection bali jungle camping, dukuh retreat, green oasis.

`;

                btn.addEventListener('click', async () => {
                    results.innerHTML = '';
                    loader.style.display = 'block';

                    try {
                        const response = await fetch('/wp-json/ai/v1/tabanan', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                prompt: textarea.value
                            })
                        });

                  const data = await response.json();

// Use `data.reply` instead of `data.choices[0].message.content`
const text = data.reply || 'No response';

// Update HTML
results.innerHTML = `
<div class="tabanan-card">
    <div class="tabanan-card-content">
        <h3>PHRI Recommendation</h3>
        <p>${text.replace(/\n/g, '<br>')}</p>
    </div>
</div>
`;

                        results.appendChild(card);

                        const resultEl = card.querySelector('.typing-result');
                        typeText(resultEl, text, 20);

                    } catch (e) {
                        // alert('Failed to fetch AI response');
                    }

                    loader.style.display = 'none';
                });
            })();
        </script>

        <?php
    }
}


