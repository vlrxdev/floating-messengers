<?php
if (!defined('ABSPATH')) {
    exit;
}

function fm_button_types() {
    return [
        'telegram' => [
            'label'       => 'Telegram',
            'color'       => '#229ed9',
            'placeholder' => 'https://t.me/username',
            'hint'        => 'Ссылка на профиль или чат, например https://t.me/username',
        ],
        'whatsapp' => [
            'label'       => 'WhatsApp',
            'color'       => '#25d366',
            'placeholder' => '79001234567',
            'hint'        => 'Номер с кодом страны без + и пробелов, или ссылка https://wa.me/79001234567',
        ],
        'viber' => [
            'label'       => 'Viber',
            'color'       => '#7360f2',
            'placeholder' => 'viber://chat?number=%2B79001234567',
            'hint'        => 'Ссылка Viber или номер телефона',
        ],
        'max' => [
            'label'       => 'MAX',
            'color'       => '#471AFF',
            'placeholder' => 'https://max.ru/username',
            'hint'        => 'Ссылка на профиль MAX',
        ],
        'phone' => [
            'label'       => 'Телефон',
            'color'       => '#34a853',
            'placeholder' => '+79001234567',
            'hint'        => 'Номер телефона для звонка',
        ],
        'email' => [
            'label'       => 'Email',
            'color'       => '#ea4335',
            'placeholder' => 'hello@example.com',
            'hint'        => 'Адрес электронной почты',
        ],
        'custom' => [
            'label'       => 'Своя ссылка',
            'color'       => '#555555',
            'placeholder' => 'https://example.com',
            'hint'        => 'Любая ссылка',
        ],
    ];
}

function fm_default_buttons() {
    return [
        [
            'enabled' => 1,
            'type'    => 'telegram',
            'label'   => 'Telegram',
            'value'   => 'https://t.me/',
            'color'   => '#229ed9',
        ],
        [
            'enabled' => 1,
            'type'    => 'whatsapp',
            'label'   => 'WhatsApp',
            'value'   => '',
            'color'   => '#25d366',
        ],
    ];
}

function fm_get_settings() {
    $option = get_option(FM_OPTION_KEY, []);

    if (!isset($option['buttons']) && (isset($option['telegram_url']) || isset($option['whatsapp_phone']))) {
        $buttons = [];

        if (!empty($option['telegram_enabled']) || !empty($option['telegram_url'])) {
            $buttons[] = [
                'enabled' => !empty($option['telegram_enabled']) ? 1 : 0,
                'type'    => 'telegram',
                'label'   => 'Telegram',
                'value'   => $option['telegram_url'] ?? '',
                'color'   => '#229ed9',
            ];
        }

        if (!empty($option['whatsapp_enabled']) || !empty($option['whatsapp_phone'])) {
            $value = $option['whatsapp_phone'] ?? '';
            if (!empty($option['whatsapp_text']) && $value !== '') {
                $value = 'https://wa.me/' . preg_replace('/\D+/', '', $value) . '?text=' . rawurlencode($option['whatsapp_text']);
            }

            $buttons[] = [
                'enabled' => !empty($option['whatsapp_enabled']) ? 1 : 0,
                'type'    => 'whatsapp',
                'label'   => 'WhatsApp',
                'value'   => $value,
                'color'   => '#25d366',
            ];
        }

        $option = ['buttons' => $buttons];
        update_option(FM_OPTION_KEY, $option);
    }

    if (empty($option['buttons']) || !is_array($option['buttons'])) {
        $option['buttons'] = fm_default_buttons();
    }

    $size = isset($option['icon_size']) ? (int) $option['icon_size'] : 56;
    $option['icon_size'] = max(32, min(96, $size));

    $positions = array_keys(fm_positions());
    $position  = sanitize_key($option['position'] ?? 'bottom-right');
    $option['position'] = in_array($position, $positions, true) ? $position : 'bottom-right';

    $option['offset_x'] = max(0, min(200, isset($option['offset_x']) ? (int) $option['offset_x'] : 20));
    $option['offset_y'] = max(0, min(200, isset($option['offset_y']) ? (int) $option['offset_y'] : 20));

    return $option;
}

function fm_positions() {
    return [
        'bottom-right' => 'Справа снизу',
        'bottom-left'  => 'Слева снизу',
        'top-right'    => 'Справа сверху',
        'top-left'     => 'Слева сверху',
    ];
}

function fm_get_buttons() {
    $settings = fm_get_settings();
    return is_array($settings['buttons'] ?? null) ? $settings['buttons'] : [];
}

function fm_get_icon_size() {
    $settings = fm_get_settings();
    return (int) ($settings['icon_size'] ?? 56);
}

function fm_get_position() {
    $settings = fm_get_settings();
    return $settings['position'] ?? 'bottom-right';
}

function fm_get_offsets() {
    $settings = fm_get_settings();
    return [
        'x' => (int) ($settings['offset_x'] ?? 20),
        'y' => (int) ($settings['offset_y'] ?? 20),
    ];
}

function fm_build_button_url($button) {
    $type  = $button['type'] ?? 'custom';
    $value = trim((string) ($button['value'] ?? ''));

    if ($value === '') {
        return '';
    }

    switch ($type) {
        case 'whatsapp':
            if (preg_match('#^https?://#i', $value) || str_starts_with($value, 'whatsapp:')) {
                return $value;
            }
            $phone = preg_replace('/\D+/', '', $value);
            return $phone !== '' ? 'https://wa.me/' . $phone : '';

        case 'phone':
            if (str_starts_with($value, 'tel:')) {
                return $value;
            }
            return 'tel:' . preg_replace('/[^\d+]/', '', $value);

        case 'email':
            if (str_starts_with($value, 'mailto:')) {
                return $value;
            }
            return 'mailto:' . sanitize_email($value);

        case 'viber':
            if (str_starts_with($value, 'viber:') || preg_match('#^https?://#i', $value)) {
                return $value;
            }
            $phone = preg_replace('/\D+/', '', $value);
            return $phone !== '' ? 'viber://chat?number=%2B' . $phone : '';

        default:
            return $value;
    }
}

function fm_get_icon_svg($type) {
    if ($type === 'max') {
        return '<svg viewBox="0 0 720 720" aria-hidden="true" focusable="false"><path fill="currentColor" d="M350.4,9.6C141.8,20.5,4.1,184.1,12.8,390.4c3.8,90.3,40.1,168,48.7,253.7,2.2,22.2-4.2,49.6,21.4,59.3,31.5,11.9,79.8-8.1,106.2-26.4,9-6.1,17.6-13.2,24.2-22,27.3,18.1,53.2,35.6,85.7,43.4,143.1,34.3,299.9-44.2,369.6-170.3C799.6,291.2,622.5-4.6,350.4,9.6h0ZM269.4,504c-11.3,8.8-22.2,20.8-34.7,27.7-18.1,9.7-23.7-.4-30.5-16.4-21.4-50.9-24-137.6-11.5-190.9,16.8-72.5,72.9-136.3,150-143.1,78-6.9,150.4,32.7,183.1,104.2,72.4,159.1-112.9,316.2-256.4,218.6h0Z"/></svg>';
    }

    $icons = [
        'telegram' => '<path fill="currentColor" d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>',
        'whatsapp' => '<path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>',
        'viber'    => '<path fill="currentColor" d="M11.4 0C9.1.1 5.5.5 3.1 2.8 1.2 4.7.6 7.4.5 9.8c-.2 3.8.4 8.4 3.3 10.6.3.2.5.1.6-.2l.5-1.8c.1-.3 0-.5-.2-.7-.7-.8-1.2-1.9-1.3-3.4-.2-2.8.3-6.3 2.8-8.4C7.7 4.5 9.9 4 11.5 4c3.7 0 6.6 1.5 7.8 4.1 1 2.2.8 5.1-.2 7.1-.8 1.6-2.2 2.6-3.8 2.8-1 .1-1.9-.1-2.7-.6l-1.7 1.1c1.4.9 3 1.3 4.6 1.2 2.3-.2 4.4-1.6 5.6-3.9 1.4-2.7 1.7-6.5.3-9.5C19.6 2.6 15.7 0 11.4 0zm.2 5.5c-.4 0-.7.3-.7.7v.1c0 .4.3.7.7.7.2 0 3.1.1 4.5 1.6 1.3 1.4 1.5 3.6 1.5 4.4 0 .4.3.7.7.7s.7-.3.7-.7c0-.9-.2-3.5-1.8-5.2-1.8-1.9-5.2-2.3-5.6-2.3zm0 2.8c-.4 0-.7.3-.7.7s.3.7.7.7c.1 0 1.6.1 2.3.9.7.7.8 1.8.8 2.1 0 .4.3.7.7.7s.7-.3.7-.7c0-.6-.2-2.1-1.3-3.2-.9-1-2.6-1.2-3.2-1.2zm1.4 3.2c-.9 0-1.6.7-1.6 1.6 0 .9.7 1.6 1.6 1.6s1.6-.7 1.6-1.6-.7-1.6-1.6-1.6z"/>',
        'phone'    => '<path fill="currentColor" d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.2 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1l-2.2 2.9z"/>',
        'email'    => '<path fill="currentColor" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5L4 8V6l8 5 8-5v2z"/>',
        'custom'   => '<path fill="currentColor" d="M3.9 12c0-1.7 1.4-3.1 3.1-3.1h4V7H7c-2.8 0-5 2.2-5 5s2.2 5 5 5h4v-1.9H7c-1.7 0-3.1-1.4-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.7 0 3.1 1.4 3.1 3.1s-1.4 3.1-3.1 3.1h-4V17h4c2.8 0 5-2.2 5-5s-2.2-5-5-5z"/>',
    ];

    $path = $icons[$type] ?? $icons['custom'];

    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $path . '</svg>';
}
