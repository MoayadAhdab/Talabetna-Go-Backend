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
        $types = BusinessType::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (BusinessType $type) => [
                'id' => (int) $type->id,
                'name' => (string) ($type->name ?? ''),
                'slug' => (string) ($type->slug ?? ''),
                'description' => (string) ($type->description ?? ''),
                'icon' => (string) ($type->icon ?? 'heroicon-o-building-storefront'),
                'image' => $this->imageUrl(
                    $type->image,
                    $type->slug === 'coffee'
                        ? 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=85'
                        : 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=85'
                ),
                'status' => (bool) $type->status,
                'sort_order' => (int) ($type->sort_order ?? 0),
                'settings' => [
                    'supports_pickup' => true,
                    'supports_delivery' => true,
                    'supports_scheduled_orders' => true,
                ],
                'created_at' => $type->created_at?->toIso8601String() ?? '',
                'updated_at' => $type->updated_at?->toIso8601String() ?? '',
            ])
            ->values();

        return response()->json([
            'data' => $types,
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
        abort_unless(
            $business->is_active,
            404
        );

        return response()->json([
            'data' => [
                'id' => (int) $business->id,
                'business_type_id' => (int) ($business->business_type_id ?? 0),

                'name' => (string) ($business->name ?? ''),
                'slug' => (string) ($business->slug ?? ''),

                'description' => (string) ($business->description ?? ''),

                'logo' => $this->imageUrl(
                    $business->logo,
                    $this->merchantFallbackImage($business)
                ),

                'cover_image' => $this->imageUrl(
                    $business->cover_image,
                    $this->merchantFallbackImage($business)
                ),

                'phone' => (string) ($business->phone ?? ''),
                'email' => (string) ($business->email ?? ''),

                'address' => (string) ($business->address ?? ''),
                'city' => (string) ($business->city ?? ''),

                'latitude' => (float) ($business->latitude ?? 0),
                'longitude' => (float) ($business->longitude ?? 0),

                'commission_rate' => (float) ($business->commission_rate ?? 0),

                'is_active' => (bool) $business->is_active,
                'is_featured' => (bool) $business->is_featured,

                'sort_order' => (int) ($business->sort_order ?? 0),

                'settings' => is_array($business->settings)
                    ? $business->settings
                    : [],

                'created_at' =>
                    $business->created_at?->toIso8601String() ?? '',

                'updated_at' =>
                    $business->updated_at?->toIso8601String() ?? '',
            ],
        ]);
    }

    public function businessDetailsFromBody(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_id' => [
                'required',
                'integer',
                'exists:businesses,id',
            ],
        ]);

        $business = Business::query()
            ->where('id', $validated['business_id'])
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id' => (int) $business->id,
                'business_type_id' => (int) ($business->business_type_id ?? 0),

                'name' => (string) ($business->name ?? ''),
                'slug' => (string) ($business->slug ?? ''),

                'description' => (string) ($business->description ?? ''),

                'logo' => $this->imageUrl(
                    $business->logo,
                    $this->merchantFallbackImage($business)
                ),

                'cover_image' => $this->imageUrl(
                    $business->cover_image,
                    $this->merchantFallbackImage($business)
                ),

                'phone' => (string) ($business->phone ?? ''),
                'email' => (string) ($business->email ?? ''),

                'address' => (string) ($business->address ?? ''),
                'city' => (string) ($business->city ?? ''),

                'latitude' => (float) ($business->latitude ?? 0),
                'longitude' => (float) ($business->longitude ?? 0),

                'commission_rate' =>
                    (float) ($business->commission_rate ?? 0),

                'is_active' =>
                    (bool) $business->is_active,

                'is_featured' =>
                    (bool) $business->is_featured,

                'sort_order' =>
                    (int) ($business->sort_order ?? 0),

                'settings' =>
                    is_array($business->settings)
                        ? $business->settings
                        : [],

                'created_at' =>
                    $business->created_at?->toIso8601String() ?? '',

                'updated_at' =>
                    $business->updated_at?->toIso8601String() ?? '',
            ],
        ]);
    }

    public function categories(Business $business): JsonResponse
    {
        $categories = $business->categories()
            ->with([
                'children' => fn ($query) =>
                    $query
                        ->where('is_active', true)
                        ->orderBy('sort_order'),
            ])
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $categories->map(function (Category $category) {
                return [
                    'id' => (int) $category->id,
                    'name' => (string) ($category->name ?? ''),
                    'slug' => (string) ($category->slug ?? ''),
                    'description' => (string) ($category->description ?? ''),
                    'image' => (string) ($category->image ?? ''),

                    'subcategories' => $category->children
                        ->map(function (Category $child) {
                            return [
                                'id' => (int) $child->id,
                                'name' => (string) ($child->name ?? ''),
                                'slug' => (string) ($child->slug ?? ''),
                                'description' => (string) ($child->description ?? ''),
                                'image' => (string) ($child->image ?? ''),
                            ];
                        })
                        ->values(),
                ];
            })->values(),
        ]);
    }

    public function categoryProducts(
        Business $business,
        Category $category
    ): JsonResponse {
        abort_unless(
            $category->business_id === $business->id,
            404
        );

        $categoryIds = collect([
            $category->id,
            ...$category->children()
                ->where('is_active', true)
                ->pluck('id')
                ->all(),
        ]);

        $products = Product::query()
            ->with([
                'category',
                'modifierGroups.options',
            ])
            ->where('business_id', $business->id)
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => [
                'category' => [
                    'id' => (int) $category->id,
                    'name' => (string) ($category->name ?? ''),
                    'slug' => (string) ($category->slug ?? ''),
                ],

                'subcategories' => $category->children
                    ->where('is_active', true)
                    ->values()
                    ->map(function (Category $subCategory) use ($products) {
                        return [
                            'id' => (int) $subCategory->id,
                            'name' => (string) ($subCategory->name ?? ''),
                            'slug' => (string) ($subCategory->slug ?? ''),

                            'products' => $products
                                ->where(
                                    'category_id',
                                    $subCategory->id
                                )
                                ->values(),
                        ];
                    })
                    ->values(),

                'products' => $products
                    ->where('category_id', $category->id)
                    ->values(),
            ],
        ]);
    }

    /**
     * Mobile compatibility endpoint.
     *
     * POST /api/v1/catalog/categories
     *
     * Body: { "business_id": 1 }
     */
    public function categoriesFromBody(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_id' => [
                'required',
                'integer',
                'exists:businesses,id',
            ],
        ]);

        return $this->categories(
            Business::query()->findOrFail(
                $validated['business_id']
            )
        );
    }

 public function products(
    Request $request,
    Business $business
): JsonResponse {
    $query = Product::query()
        ->with([
            'category',
            'modifierGroups' => function ($query) {
                $query->orderByDesc('is_required')
                      ->orderBy('sort_order');
            },
            'modifierGroups.options',
        ])
        ->where('business_id', $business->id)
        ->where('is_active', true)
        ->where('is_available', true)
        ->orderBy('sort_order');

    if ($request->filled('category_id')) {
        $category = $business->categories()
            ->findOrFail(
                $request->integer('category_id')
            );

        $categoryIds = [$category->id];

        if ($category->parent_id === null) {
            $categoryIds = array_merge(
                $categoryIds,
                $category->children()->pluck('id')->all()
            );
        }

        $query->whereIn('category_id', $categoryIds);
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

    /**
     * Mobile compatibility endpoint.
     *
     * POST /api/v1/catalog/products
     *
     * Body: { "business_id": 1, "category_id": 3 }
     */
    public function productsFromBody(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_id' => [
                'required',
                'integer',
                'exists:businesses,id',
            ],

            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $catalogRequest = Request::create(
            '/',
            'GET',
            array_filter([
                'category_id' => $validated['category_id'] ?? null,
                'per_page' => $validated['per_page'] ?? null,
            ], fn ($value) => $value !== null)
        );

        return $this->products(
            $catalogRequest,
            Business::query()->findOrFail(
                $validated['business_id']
            )
        );
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
                    'id' => (int) $business->id,
                    'name' => (string) ($business->name ?? ''),
                    'slug' => (string) ($business->slug ?? ''),
                    'description' => (string) ($business->description ?? ''),

                    'logo' => (string) ($business->logo ?? ''),
                    'cover_image' => (string) ($business->cover_image ?? ''),

                    'city' => (string) ($business->city ?? ''),

                    'business_type' => [
                        'id' => (int) ($business->businessType?->id ?? 0),
                        'name' => (string) ($business->businessType?->name ?? ''),
                        'image' => (string) ($business->businessType?->image ?? ''),
                    ],

                    'is_featured' => (bool) $business->is_featured,
                ];
            })->values(),
        ]);
    }

    public function topBanners(): JsonResponse
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
                    'id' => (int) $banner->id,
                    'title' => (string) ($banner->title ?? ''),
                    'subtitle' => (string) ($banner->subtitle ?? ''),
                    'image' => (string) ($banner->image ?? ''),
                    'mobile_image' => (string) ($banner->mobile_image ?? ''),
                    'placement' => (string) ($banner->placement ?? ''),

                    'link' => [
                        'type' => (string) ($banner->link_type ?? ''),
                        'value' => (string) ($banner->link_value ?? ''),
                    ],

                    'merchant' => $banner->business
                        ? [
                            'id' => (int) $banner->business->id,
                            'name' => (string) ($banner->business->name ?? ''),
                            'slug' => (string) ($banner->business->slug ?? ''),
                            'logo' => (string) ($banner->business->logo ?? ''),
                        ]
                        : [],

                    'sort_order' => (int) ($banner->sort_order ?? 0),
                ];
            })->values(),
        ]);
    }

    public function featuredMerchants(): JsonResponse
    {
        $merchants = Business::query()
            ->with([
                'businessType',
                'products' => fn ($query) => $query
                    ->where('is_active', true)
                    ->where('is_available', true)
                    ->orderBy('sort_order'),
            ])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $merchants->map(function (Business $business) {
                return [
                    'id' => (int) $business->id,
                    'name' => (string) ($business->name ?? ''),
                    'slug' => (string) ($business->slug ?? ''),

                    'logo' => $this->imageUrl(
                        $business->logo,
                        $this->merchantFallbackImage($business)
                    ),

                    'cover_image' => $this->imageUrl(
                        $business->cover_image,
                        $this->merchantFallbackImage($business)
                    ),

                    'description' => (string) ($business->description ?? ''),

                    'phone' => (string) ($business->phone ?? ''),

                    'city' => (string) ($business->city ?? 'Tripoli'),

                    'latitude' => (float) ($business->latitude ?? 34.4367),

                    'longitude' => (float) ($business->longitude ?? 35.8497),

                    'is_featured' => (bool) $business->is_featured,

                    'category' =>
                        (string) ($business->businessType?->name ?? 'Restaurant'),

                    'eta' => '25–35 min',

                    'fee' => 1.50,

                    'minimum' => 5.00,

                    'products' => $business->products
                        ->map(fn (Product $product) => [
                            'name' => (string) ($product->name ?? ''),
                            'description' => (string) ($product->description ?? ''),
                            'price' => (float) ($product->sale_price ?? $product->price ?? 0),
                            'image' => $this->imageUrl(
                                $product->image,
                                $this->merchantFallbackImage($business)
                            ),
                            'customizable' => false,
                        ])
                        ->values(),

                    'business_type' => $business->businessType
                        ? [
                            'id' => (int) $business->businessType->id,
                            'name' => (string) ($business->businessType->name ?? ''),
                            'slug' => (string) ($business->businessType->slug ?? ''),
                            'image' => $this->imageUrl(
                                $business->businessType->image,
                                $this->merchantFallbackImage($business)
                            ),
                        ]
                        : [
                            'id' => 0,
                            'name' => 'Restaurant',
                            'slug' => 'restaurant',
                            'image' => $this->merchantFallbackImage($business),
                        ],
                ];
            })->values(),
        ]);
    }

    private function merchantFallbackImage(Business $business): string
    {
        return $business->businessType?->slug === 'coffee'
            ? 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=85'
            : 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=85';
    }

    private function imageUrl(?string $image, string $fallback): string
    {
        if (! $image) {
            return $fallback;
        }

        if (
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://')
        ) {
            return $image;
        }

        return asset('storage/' . ltrim($image, '/'));
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
        'modifierGroups' => fn ($query) => $query
            ->orderByDesc('is_required')
            ->orderBy('sort_order'),

        'modifierGroups.options' => fn ($query) => $query
            ->orderBy('sort_order'),
    ]);

    return response()->json([
        'data' => $product,
    ]);
}

    /**
     * Mobile compatibility endpoint.
     *
     * POST /api/v1/catalog/product-details
     *
     * Body: { "product_id": 7 }
     */
    public function productFromBody(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
        ]);

        return $this->product(
            Product::query()->findOrFail(
                $validated['product_id']
            )
        );
    }
}