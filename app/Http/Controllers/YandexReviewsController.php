<?php

namespace App\Http\Controllers;

use App\Services\YandexReviewsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YandexReviewsController extends Controller
{
    public function __construct(
        private YandexReviewsService $reviewsService
    ) {}

    public function index(Request $request): Response
    {
        $data = $this->reviewsService->getReviewsForUser($request->user());

        return Inertia::render('Yandex/Reviews', [
            'rating' => $data['rating'],
            'reviewsCount' => $data['reviews_count'],
            'companyName' => $data['company_name'],
            'reviews' => $data['reviews'],
            'error' => $data['error'] ?? null,
        ]);
    }

    public function refresh(Request $request)
    {
        $data = $this->reviewsService->refreshForUser($request->user());

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return back()->with([
                'rating' => $data['rating'],
                'reviewsCount' => $data['reviews_count'],
                'companyName' => $data['company_name'],
                'reviews' => $data['reviews'],
                'error' => $data['error'] ?? null,
                'success' => empty($data['error']) ? 'Данные обновлены.' : null,
            ]);
        }

        return back();
    }
}
