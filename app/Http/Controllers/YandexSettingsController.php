<?php

namespace App\Http\Controllers;

use App\Models\YandexSetting;
use App\Services\YandexUrlParser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YandexSettingsController extends Controller
{
    public function __construct(
        private YandexUrlParser $urlParser
    ) {}

    public function index(Request $request): Response
    {
        $setting = $request->user()->yandexSetting;

        return Inertia::render('Yandex/Settings', [
            'yandexUrl' => $setting?->yandex_url ?? '',
            'orgId' => $setting?->org_id ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'yandex_url' => ['required', 'string', 'max:2000'],
        ]);

        $url = trim($request->input('yandex_url'));

        if (!YandexUrlParser::isValidYandexMapsUrl($url)) {
            return back()->withErrors([
                'yandex_url' => 'Укажите корректную ссылку на организацию в Яндекс.Картах (yandex.ru/maps/...).',
            ]);
        }

        $orgId = YandexUrlParser::parseOrgId($url);
        if (!$orgId) {
            return back()->withErrors([
                'yandex_url' => 'Не удалось определить организацию по ссылке. Используйте ссылку вида: https://yandex.ru/maps/org/название/... или с параметром oid=...',
            ]);
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        YandexSetting::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'yandex_url' => $url,
                'org_id' => $orgId,
            ]
        );

        return back()->with('success', 'Настройки сохранены. Теперь вы можете просматривать отзывы на странице «Отзывы».');
    }
}
