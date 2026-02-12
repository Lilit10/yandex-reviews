<?php

namespace App\Services;

use App\Models\User;
use App\Models\YandexReviewsCache;
use App\Models\YandexSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexReviewsService
{
    private const CACHE_TTL_MINUTES = 60;

    public function __construct(
        private YandexUrlParser $urlParser
    ) {}

    public function getReviewsForUser(User $user): array
    {
        $setting = $user->yandexSetting;
        if (!$setting || !$setting->yandex_url || !$setting->org_id) {
            return [
                'rating' => null,
                'reviews_count' => 0,
                'company_name' => null,
                'reviews' => [],
                'error' => 'Укажите ссылку на карточку организации в настройках.',
            ];
        }

        $cache = YandexReviewsCache::where('user_id', $user->id)
            ->where('org_id', $setting->org_id)
            ->first();

        if ($cache && $cache->cached_at && $cache->cached_at->diffInMinutes(now()) < self::CACHE_TTL_MINUTES) {
            return [
                'rating' => (float) $cache->rating,
                'reviews_count' => (int) $cache->reviews_count,
                'company_name' => $cache->company_name,
                'reviews' => $cache->reviews ?? [],
            ];
        }

        return $this->fetchAndCache($user, $setting->org_id, $setting->yandex_url);
    }

    public function refreshForUser(User $user): array
    {
        $setting = $user->yandexSetting;
        if (!$setting || !$setting->org_id || !$setting->yandex_url) {
            return [
                'rating' => null,
                'reviews_count' => 0,
                'company_name' => null,
                'reviews' => [],
                'error' => 'Укажите ссылку на карточку в настройках.',
            ];
        }

        return $this->fetchAndCache($user, $setting->org_id, $setting->yandex_url, true);
    }

    private function fetchAndCache(User $user, string $orgId, string $pageUrl, bool $forceRefresh = false): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8',
                ])
                ->get($pageUrl);

            if (!$response->successful()) {
                Log::warning('Yandex Maps page request failed', ['url' => $pageUrl, 'status' => $response->status()]);
                return $this->fallbackFromCache($user, $orgId) + [
                    'error' => 'Не удалось загрузить страницу организации. Проверьте ссылку.',
                ];
            }

            $html = $response->body();
            $data = $this->extractDataFromHtml($html, $orgId);

            YandexReviewsCache::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'org_id' => $orgId,
                ],
                [
                    'rating' => $data['rating'],
                    'reviews_count' => $data['reviews_count'],
                    'company_name' => $data['company_name'],
                    'reviews' => $data['reviews'],
                    'cached_at' => now(),
                ]
            );

            return [
                'rating' => $data['rating'],
                'reviews_count' => $data['reviews_count'],
                'company_name' => $data['company_name'],
                'reviews' => $data['reviews'],
            ];
        } catch (\Throwable $e) {
            Log::error('YandexReviewsService fetch error', ['message' => $e->getMessage(), 'org_id' => $orgId]);
            return $this->fallbackFromCache($user, $orgId) + [
                'error' => 'Ошибка при получении данных: ' . $e->getMessage(),
            ];
        }
    }

    private function extractDataFromHtml(string $html, string $orgId): array
    {
        $default = [
            'rating' => null,
            'reviews_count' => 0,
            'company_name' => null,
            'reviews' => [],
        ];

        if (preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>([\s\S]*?)<\/script>/i', $html, $matches)) {
            foreach ($matches[1] as $jsonStr) {
                $json = json_decode(trim($jsonStr), true);
                if (!is_array($json)) {
                    continue;
                }
                $entities = isset($json['@graph']) && is_array($json['@graph']) ? $json['@graph'] : [$json];
                foreach ($entities as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    if (isset($item['aggregateRating'])) {
                        $ar = $item['aggregateRating'];
                        if ($default['rating'] === null && isset($ar['ratingValue'])) {
                            $default['rating'] = (float) $ar['ratingValue'];
                        }
                        if (isset($ar['reviewCount'])) {
                            $default['reviews_count'] = (int) $ar['reviewCount'];
                        }
                    }
                    if (!empty($item['name']) && $default['company_name'] === null) {
                        $default['company_name'] = $item['name'];
                    }
                    if (!empty($item['review']) && is_array($item['review'])) {
                        foreach ($item['review'] as $r) {
                            $default['reviews'][] = $this->normalizeReviewFromJsonLd($r);
                        }
                    }
                }
            }
        }

        $this->extractReviewsFromJsonBlobs($html, $default);

        if (preg_match('/"rating"\s*:\s*([\d.]+)/', $html, $m) && $default['rating'] === null) {
            $default['rating'] = (float) $m[1];
        }
        if (preg_match('/"reviewsCount"\s*:\s*(\d+)/', $html, $m)) {
            $default['reviews_count'] = (int) $m[1];
        }
        if (preg_match('/"reviewCount"\s*:\s*(\d+)/', $html, $m) && $default['reviews_count'] === 0) {
            $default['reviews_count'] = (int) $m[1];
        }
        if (preg_match('/<h1[^>]*class="[^"]*business-heading[^"]*"[^>]*>([^<]+)</', $html, $m) && $default['company_name'] === null) {
            $default['company_name'] = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return $default;
    }

    private function normalizeReviewFromJsonLd(array $r): array
    {
        $author = 'Гость';
        if (isset($r['author'])) {
            $author = is_array($r['author']) ? ($r['author']['name'] ?? $author) : (string) $r['author'];
        }
        $rating = null;
        if (isset($r['reviewRating']['ratingValue'])) {
            $rating = (int) round((float) $r['reviewRating']['ratingValue']);
        }
        return [
            'author' => $author,
            'date' => $r['datePublished'] ?? null,
            'rating' => $rating,
            'text' => $r['reviewBody'] ?? '',
            'phone' => $r['author']['telephone'] ?? null,
        ];
    }

    private function extractReviewsFromJsonBlobs(string $html, array &$default): void
    {
        $patterns = [
            '/"reviews"\s*:\s*\[(\s*\{[\s\S]*?\}\s*)\]/',
            '/"review"\s*:\s*\[(\s*\{[\s\S]*?\}\s*)\]/',
            '/"items"\s*:\s*\[(\s*\{[\s\S]*?"(?:reviewBody|text|comment)"[\s\S]*?\}\s*)\]/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $arrStr = '[' . trim($m[1]) . ']';
                $decoded = json_decode($arrStr, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $extracted = $this->parseReviewLikeArray($decoded);
                    if (!empty($extracted)) {
                        $default['reviews'] = array_merge($default['reviews'], $extracted);
                        break;
                    }
                }
            }
        }

        if (empty($default['reviews']) && preg_match_all('/\{\s*"(?:author|userName|name)"\s*:\s*"[^"]*"[^}]*"(?:reviewBody|text|comment)"\s*:\s*"[^"]*"/', $html, $m)) {
            $seen = [];
            foreach (array_slice($m[0], 0, 50) as $snippet) {
                if (preg_match('/"(?:author|userName|name)"\s*:\s*"([^"]*)"/', $snippet, $auth)) {
                    if (preg_match('/"(?:reviewBody|text|comment)"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/', $snippet, $body)) {
                        $key = $auth[1] . '|' . substr($body[1], 0, 50);
                        if (!isset($seen[$key])) {
                            $seen[$key] = true;
                            $default['reviews'][] = [
                                'author' => $auth[1],
                                'date' => null,
                                'rating' => null,
                                'text' => stripcslashes($body[1]),
                                'phone' => null,
                            ];
                        }
                    }
                }
            }
        }
    }

    private function parseReviewLikeArray(array $arr): array
    {
        $out = [];
        foreach ($arr as $r) {
            if (!is_array($r)) {
                continue;
            }
            $text = $r['reviewBody'] ?? $r['text'] ?? $r['comment'] ?? $r['body'] ?? '';
            $author = $r['author']['name'] ?? $r['author'] ?? $r['userName'] ?? $r['name'] ?? 'Гость';
            if (is_array($author)) {
                $author = $author['name'] ?? 'Гость';
            }
            $date = $r['datePublished'] ?? $r['date'] ?? $r['createdAt'] ?? null;
            $rating = null;
            if (isset($r['reviewRating']['ratingValue'])) {
                $rating = (int) round((float) $r['reviewRating']['ratingValue']);
            } elseif (isset($r['rating'])) {
                $rating = (int) round((float) $r['rating']);
            }
            $out[] = [
                'author' => $author,
                'date' => $date,
                'rating' => $rating,
                'text' => $text,
                'phone' => $r['author']['telephone'] ?? $r['phone'] ?? null,
            ];
        }
        return $out;
    }

    private function fallbackFromCache(User $user, string $orgId): array
    {
        $cache = YandexReviewsCache::where('user_id', $user->id)->where('org_id', $orgId)->first();
        if ($cache) {
            return [
                'rating' => (float) $cache->rating,
                'reviews_count' => (int) $cache->reviews_count,
                'company_name' => $cache->company_name,
                'reviews' => $cache->reviews ?? [],
            ];
        }
        return [
            'rating' => null,
            'reviews_count' => 0,
            'company_name' => null,
            'reviews' => [],
        ];
    }
}
