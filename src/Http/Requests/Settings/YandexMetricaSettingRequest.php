<?php

namespace Botble\YandexMetrica\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class YandexMetricaSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'yandex_metrica_enabled' => new OnOffRule(),
            'yandex_metrica_counter_id' => ['nullable', 'string'],
            'yandex_metrica_auth_code' => ['nullable', 'string'],
            'yandex_metrica_track_logged_in' => new OnOffRule(),
            'yandex_metrica_ignore_roles' => ['nullable'],
            'yandex_metrica_dashboard_roles' => ['nullable'],
            'yandex_metrica_js_url' => ['nullable', 'string', 'url'],
        ];
    }
}
