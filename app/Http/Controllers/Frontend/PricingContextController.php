<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\FrontendPricingContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingContextController extends Controller
{
    public function __construct(private readonly FrontendPricingContextResolver $frontendPricingContextResolver)
    {
    }

    public function __invoke(Request $request, string $locale): JsonResponse
    {
        return response()->json([
            'pricing' => $this->frontendPricingContextResolver->resolve($request),
        ]);
    }
}
