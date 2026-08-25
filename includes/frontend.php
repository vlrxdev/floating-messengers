<?php
if (!defined('ABSPATH')) {
    exit;
}

function fm_get_visible_buttons() {
    $visible = [];

    foreach (fm_get_buttons() as $button) {
        if (empty($button['enabled'])) {
            continue;
        }

        $url = fm_build_button_url($button);
        if ($url === '') {
            continue;
        }

        $button['url'] = $url;
        $visible[] = $button;
    }

    return $visible;
}

add_action('wp_enqueue_scripts', function () {
    if (is_admin() || empty(fm_get_visible_buttons())) {
        return;
    }

    wp_enqueue_style(
        'floating-messengers',
        FM_PLUGIN_URL . 'assets/style.css',
        [],
        FM_VERSION
    );

    $size      = fm_get_icon_size();
    $icon_size = (int) round($size * 0.5);
    $gap       = max(8, (int) round($size * 0.2));
    $offsets   = fm_get_offsets();

    wp_add_inline_style(
        'floating-messengers',
        sprintf(
            '.fm-buttons{--fm-btn-size:%1$dpx;--fm-icon-size:%2$dpx;--fm-gap:%3$dpx;--fm-offset-x:%4$dpx;--fm-offset-y:%5$dpx;}',
            $size,
            $icon_size,
            $gap,
            $offsets['x'],
            $offsets['y']
        )
    );

    wp_enqueue_script(
        'floating-messengers',
        FM_PLUGIN_URL . 'assets/frontend.js',
        [],
        FM_VERSION,
        true
    );
});

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }

    $buttons = fm_get_visible_buttons();
    if (empty($buttons)) {
        return;
    }

    $position = fm_get_position();
    ?>
    <div class="fm-buttons fm-buttons--<?php echo esc_attr($position); ?>" aria-label="Связаться с нами">
        <?php foreach ($buttons as $button) :
            $label = $button['label'] !== '' ? $button['label'] : 'Связаться';
            $type  = $button['type'] ?? 'custom';
            $color = $button['color'] ?? '#555555';
            ?>
            <a class="fm-btn fm-btn--<?php echo esc_attr($type); ?>"
               href="<?php echo esc_url($button['url']); ?>"
               style="background-color: <?php echo esc_attr($color); ?>;"
               target="_blank"
               rel="noopener noreferrer"
               aria-label="<?php echo esc_attr($label); ?>"
               title="<?php echo esc_attr($label); ?>">
                <?php echo fm_get_icon_svg($type); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
});
