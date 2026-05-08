<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Slider;
use App\Support\FrontendCatalogPresenter;
use App\Support\LocalizedQuery;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $featuredCategories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->with([
                'products' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with(['images', 'variants', 'categories'])
                    ->latest()
                    ->limit(4),
            ])
            ->orderBy('sort_order')
            ->orderByRaw(LocalizedQuery::expression('name'))
            ->limit(6)
            ->get()
            ->map(function (Category $category): Category {
                $category->setRelation('products', FrontendCatalogPresenter::products($category->products));

                return FrontendCatalogPresenter::category($category);
            });

        $featuredProducts = FrontendCatalogPresenter::products(
            Product::query()
                ->with(['categories', 'images', 'variants'])
                ->where('is_active', true)
                ->where('is_featured', true)
                ->latest()
                ->limit(8)
                ->get()
        );

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = FrontendCatalogPresenter::products(
                Product::query()
                    ->with(['categories', 'images', 'variants'])
                    ->where('is_active', true)
                    ->latest()
                    ->limit(8)
                    ->get()
            );
        }

        $newArrivalProducts = FrontendCatalogPresenter::products(
            Product::query()
                ->with(['categories', 'images', 'variants'])
                ->where('is_active', true)
                ->latest()
                ->limit(8)
                ->get()
        );

        return response()->json([
            'sliders' => Slider::query()
                ->where('is_active', true)
                ->latest()
                ->limit(3)
                ->get()
                ->map(fn (Slider $slider) => [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'subtitle' => $slider->subtitle,
                    'image_url' => $slider->image ? asset('storage/'.$slider->image) : null,
                    'link' => $slider->link,
                    'text_color' => $slider->text_color,
                    'button_background_color' => $slider->button_background_color,
                    'button_text_color' => $slider->button_text_color,
                    'overlay_opacity_start' => $slider->overlay_opacity_start !== null ? (float) $slider->overlay_opacity_start : null,
                    'overlay_opacity_end' => $slider->overlay_opacity_end !== null ? (float) $slider->overlay_opacity_end : null,
                ])->values(),
            'featured_categories' => CategoryResource::collection($featuredCategories),
            'featured_products' => ProductResource::collection($featuredProducts),
            'new_arrival_products' => ProductResource::collection($newArrivalProducts),
            'clients' => Client::query()
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (Client $client) => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'photo_url' => $client->photo ? asset('storage/'.$client->photo) : null,
                    'position' => $client->position,
                ])->values(),
        ]);
    }
}
