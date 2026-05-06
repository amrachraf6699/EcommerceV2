<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('storefront.sitemap.xml', now()->addMinutes(60), function (): string {
            return view('frontend.sitemap', [
                'entries' => $this->entries(),
            ])->render();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * @return Collection<int, array{loc:string,lastmod:?string}>
     */
    private function entries(): Collection
    {
        $locales = array_keys(storefront_locales());
        $entries = collect();

        foreach ($locales as $locale) {
            foreach ($this->staticEntries($locale) as $entry) {
                $entries->push($entry);
            }
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['slug', 'updated_at']);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['slug', 'updated_at']);

        $pages = Page::query()
            ->orderBy('id')
            ->get(['slug', 'updated_at']);

        foreach ($locales as $locale) {
            foreach ($categories as $category) {
                $entries->push([
                    'loc' => route('storefront.categories.show', ['locale' => $locale, 'category' => $category->slug]),
                    'lastmod' => optional($category->updated_at)->toAtomString(),
                ]);
            }

            foreach ($products as $product) {
                $entries->push([
                    'loc' => route('storefront.products.show', ['locale' => $locale, 'product' => $product->slug]),
                    'lastmod' => optional($product->updated_at)->toAtomString(),
                ]);
            }

            foreach ($pages as $page) {
                $entries->push([
                    'loc' => route('storefront.pages.show', ['locale' => $locale, 'page' => $page->slug]),
                    'lastmod' => optional($page->updated_at)->toAtomString(),
                ]);
            }
        }

        return $entries;
    }

    /**
     * @return array<int, array{loc:string,lastmod:?string}>
     */
    private function staticEntries(string $locale): array
    {
        return [
            ['loc' => route('storefront.home', ['locale' => $locale]), 'lastmod' => null],
            ['loc' => route('storefront.catalog', ['locale' => $locale]), 'lastmod' => null],
            ['loc' => route('storefront.categories.index', ['locale' => $locale]), 'lastmod' => null],
            ['loc' => route('storefront.contact.show', ['locale' => $locale]), 'lastmod' => null],
        ];
    }
}
