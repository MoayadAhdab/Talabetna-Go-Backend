<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function businessTypes(): JsonResponse
    {
        return response()->json([
            'data' => BusinessType::query()
                ->where('status', true)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function businesses(Request $request): JsonResponse
    {
        $query = Business::query()
            ->with('businessType')
            ->where('is_active', true)
            ->orderBy('sort_order');

        if ($request->filled('business_type_id')) {
            $query->where(
                'business_type_id',
                $request->integer('business_type_id')
            );
        }

        if ($request->filled('city')) {
            $query->where(
                'city',
                $request->string('city')
            );
        }

        if ($request->filled('featured')) {
            $query->where(
                'is_featured',
                filter_var(
                    $request->featured,
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        return response()->json([
            'data' => $query->paginate(
                min(
                    $request->integer('per_page', 20),
                    100
                )
            ),
        ]);
    }

    public function business(Business $business): JsonResponse
    {
        $business->load([
            'businessType',
            'branches' => fn ($query) =>
                $query->where('is_active', true)
                    ->orderBy('sort_order'),
            'categories' => fn ($query) =>
                $query->where('is_active', true)
                    ->orderBy('sort_order'),
        ]);

        return response()->json([
            'data' => $business,
        ]);
    }

    public function categories(Business $business): JsonResponse
    {
        return response()->json([
            'data' => $business->categories()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function products(
        Request $request,
        Business $business
    ): JsonResponse {
        $query = Product::query()
            ->with([
                'category',
                'modifierGroups.options',
            ])
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->orderBy('sort_order');

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->integer('category_id')
            );
        }

        return response()->json([
            'data' => $query->paginate(
                min(
                    $request->integer('per_page', 20),
                    100
                )
            ),
        ]);
    }
    public function popularMerchants(): JsonResponse
{
    $merchants = Business::query()
        ->with('businessType')
        ->where('is_active', true)
        ->where('is_featured', true)
        ->orderBy('sort_order')
        ->orderByDesc('id')
        ->limit(20)
        ->get();

    return response()->json([
        'data' => $merchants->map(function (Business $business) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'description' => $business->description,
                'logo' => $business->logo,
                'cover_image' => $business->cover_image,
                'city' => $business->city,
                'business_type' => [
                    'id' => $business->businessType?->id,
                    'name' => $business->businessType?->name,
                    'image' => $business->businessType?->image,
                ],
                'is_featured' => $business->is_featured,
            ];
        }),
    ]);
}
public function topBanners(): \Illuminate\Http\JsonResponse
{
    $banners = Banner::query()
        ->with('business')
        ->where('placement', 'top')
        ->where('is_active', true)
        ->where(function ($query) {
            $query
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now());
        })
        ->where(function ($query) {
            $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now());
        })
        ->orderBy('sort_order')
        ->orderByDesc('id')
        ->get();

    return response()->json([
        'data' => $banners->map(function (Banner $banner) {
            return [
                'id' => $banner->id,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'image' => $banner->image,
                'mobile_image' => $banner->mobile_image,
                'placement' => $banner->placement,

                'link' => [
                    'type' => $banner->link_type,
                    'value' => $banner->link_value,
                ],

                'merchant' => $banner->business
                    ? [
                        'id' => $banner->business->id,
                        'name' => $banner->business->name,
                        'slug' => $banner->business->slug,
                        'logo' => $banner->business->logo,
                    ]
                    : null,

                'sort_order' => $banner->sort_order,
            ];
        })->values(),
    ]);
}
public function featuredMerchants(): JsonResponse
{
    $merchants = Business::query()
        ->with('businessType')
        ->where('is_active', true)
        ->where('is_featured', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->limit(20)
        ->get();

    return response()->json([
        'data' => $merchants->map(function (Business $business) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,

                'logo' => $business->logo,
                'cover_image' => $business->cover_image,

                'description' => $business->description,

                'phone' => $business->phone,
                'city' => $business->city,

                'latitude' => $business->latitude,
                'longitude' => $business->longitude,

                'is_featured' => $business->is_featured,

                'business_type' => $business->businessType
                    ? [
                        'id' => $business->businessType->id,
                        'name' => $business->businessType->name,
                        'slug' => $business->businessType->slug,
                        'image' => $business->businessType->image,
                    ]
                    : null,
            ];
        })->values(),
    ]);
}

    public function product(Product $product): JsonResponse
    {
        abort_unless(
            $product->is_active,
            404
        );

        $product->load([
            'business',
            'category',
            'modifierGroups.options',
        ]);

        return response()->json([
            'data' => $product,
        ]);
    }
}