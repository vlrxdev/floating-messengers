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
});

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }

    $buttons = fm_get_visible_buttons();
    if (empty($buttons)) {
        return;
    }
    ?>
    <div class="fm-buttons" aria-label="Связаться с нами">
        <?php foreach ($buttons as $button) :
            $label = $button['label'] !== '' ? $button['label'] : 'Связаться';
            $type  = $button['type'] ?? 'custom';
            $color = $button['color'] ?? '#555555';
            $is_external = !in_array($type, ['phone', 'email'], true);
            ?>
            <a class="fm-btn fm-btn--<?php echo esc_attr($type); ?>"
               href="<?php echo esc_url($button['url']); ?>"
               style="background-color: <?php echo esc_attr($color); ?>;"
               <?php if ($is_external) : ?>
                   target="_blank"
                   rel="noopener noreferrer"
               <?php endif; ?>
               aria-label="<?php echo esc_attr($label); ?>"
               title="<?php echo esc_attr($label); ?>">
                <?php echo fm_get_icon_svg($type); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
});
