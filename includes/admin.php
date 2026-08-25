<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function () {
    add_menu_page(
        'Мессенджеры',
        'Мессенджеры',
        'manage_options',
        'floating-messengers',
        'fm_render_settings_page',
        'dashicons-format-chat',
        58
    );
});

add_action('admin_init', function () {
    register_setting('floating_messengers_group', FM_OPTION_KEY, [
        'type'              => 'array',
        'sanitize_callback' => 'fm_sanitize_settings',
        'default'           => ['buttons' => []],
    ]);
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_floating-messengers') {
        return;
    }

    wp_enqueue_style(
        'floating-messengers-admin',
        FM_PLUGIN_URL . 'assets/admin.css',
        [],
        FM_VERSION
    );

    wp_enqueue_script(
        'floating-messengers-admin',
        FM_PLUGIN_URL . 'assets/admin.js',
        [],
        FM_VERSION,
        true
    );

    wp_localize_script('floating-messengers-admin', 'fmAdmin', [
        'optionKey' => FM_OPTION_KEY,
        'types'     => fm_button_types(),
    ]);
});

function fm_sanitize_settings($input) {
    $types   = array_keys(fm_button_types());
    $buttons = [];

    if (!empty($input['buttons']) && is_array($input['buttons'])) {
        foreach ($input['buttons'] as $button) {
            if (!is_array($button)) {
                continue;
            }

            $type  = sanitize_key($button['type'] ?? 'custom');
            $type  = in_array($type, $types, true) ? $type : 'custom';
            $label = sanitize_text_field($button['label'] ?? '');
            $value = trim((string) ($button['value'] ?? ''));
            $color = sanitize_hex_color($button['color'] ?? '') ?: (fm_button_types()[$type]['color'] ?? '#555555');

            if ($type === 'email') {
                $value = sanitize_email(str_replace('mailto:', '', $value));
            } elseif (in_array($type, ['telegram', 'max', 'custom'], true)) {
                $value = esc_url_raw($value);
            } else {
                $value = sanitize_text_field($value);
            }

            if ($label === '' && $value === '') {
                continue;
            }

            if ($label === '') {
                $label = fm_button_types()[$type]['label'] ?? 'Кнопка';
            }

            $buttons[] = [
                'enabled' => !empty($button['enabled']) ? 1 : 0,
                'type'    => $type,
                'label'   => $label,
                'value'   => $value,
                'color'   => $color,
            ];
        }
    }

    return ['buttons' => $buttons];
}

function fm_render_button_row($index, $button = null) {
    $types   = fm_button_types();
    $button  = wp_parse_args($button ?? [], [
        'enabled' => 1,
        'type'    => 'telegram',
        'label'   => '',
        'value'   => '',
        'color'   => '#229ed9',
    ]);
    $type    = $button['type'];
    $meta    = $types[$type] ?? $types['custom'];
    $name    = FM_OPTION_KEY . '[buttons][' . $index . ']';
    ?>
    <div class="fm-row" data-index="<?php echo esc_attr((string) $index); ?>">
        <div class="fm-row__head">
            <strong class="fm-row__title"><?php echo esc_html($button['label'] !== '' ? $button['label'] : ($meta['label'] ?? 'Кнопка')); ?></strong>
            <button type="button" class="button-link-delete fm-remove-btn">Удалить</button>
        </div>

        <div class="fm-row__grid">
            <label class="fm-field fm-field--check">
                <input type="checkbox"
                       name="<?php echo esc_attr($name); ?>[enabled]"
                       value="1"
                       <?php checked(!empty($button['enabled'])); ?>>
                Включена
            </label>

            <label class="fm-field">
                <span>Тип</span>
                <select name="<?php echo esc_attr($name); ?>[type]" class="fm-type">
                    <?php foreach ($types as $key => $item) : ?>
                        <option value="<?php echo esc_attr($key); ?>"
                            data-color="<?php echo esc_attr($item['color']); ?>"
                            data-placeholder="<?php echo esc_attr($item['placeholder']); ?>"
                            data-hint="<?php echo esc_attr($item['hint']); ?>"
                            data-label="<?php echo esc_attr($item['label']); ?>"
                            <?php selected($type, $key); ?>>
                            <?php echo esc_html($item['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="fm-field">
                <span>Название</span>
                <input type="text"
                       class="fm-label regular-text"
                       name="<?php echo esc_attr($name); ?>[label]"
                       value="<?php echo esc_attr($button['label']); ?>"
                       placeholder="<?php echo esc_attr($meta['label']); ?>">
            </label>

            <label class="fm-field fm-field--wide">
                <span>Ссылка / номер</span>
                <input type="text"
                       class="fm-value regular-text"
                       name="<?php echo esc_attr($name); ?>[value]"
                       value="<?php echo esc_attr($button['value']); ?>"
                       placeholder="<?php echo esc_attr($meta['placeholder']); ?>">
                <span class="description fm-hint"><?php echo esc_html($meta['hint']); ?></span>
            </label>

            <label class="fm-field">
                <span>Цвет</span>
                <input type="color"
                       class="fm-color"
                       name="<?php echo esc_attr($name); ?>[color]"
                       value="<?php echo esc_attr($button['color'] ?: $meta['color']); ?>">
            </label>
        </div>
    </div>
    <?php
}

function fm_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $buttons = fm_get_buttons();
    ?>
    <div class="wrap">
        <h1>Плавающие мессенджеры</h1>
        <p>Кнопки показываются справа снизу.</p>

        <form method="post" action="options.php">
            <?php settings_fields('floating_messengers_group'); ?>

            <div id="fm-buttons-list" class="fm-list">
                <?php
                if (empty($buttons)) {
                    fm_render_button_row(0, fm_default_buttons()[0]);
                } else {
                    foreach ($buttons as $index => $button) {
                        fm_render_button_row($index, $button);
                    }
                }
                ?>
            </div>

            <p>
                <button type="button" class="button" id="fm-add-btn">Добавить кнопку</button>
            </p>

            <template id="fm-row-template">
                <?php fm_render_button_row('__INDEX__', [
                    'enabled' => 1,
                    'type'    => 'custom',
                    'label'   => '',
                    'value'   => '',
                    'color'   => '#555555',
                ]); ?>
            </template>

            <?php submit_button('Сохранить'); ?>
        </form>
    </div>
    <?php
}
