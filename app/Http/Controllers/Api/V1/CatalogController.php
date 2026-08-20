<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Business Types
    |--------------------------------------------------------------------------
    */

    public function businessTypes(): JsonResponse
    {
        $types = BusinessType::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (BusinessType $type) => $this->businessTypeResponse($type))
            ->values();

        return response()->json([
            'data' => $types,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Businesses
    |--------------------------------------------------------------------------
    */

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

        $businesses = $query->paginate(
            min(
                $request->integer('per_page', 20),
                100
            )
        );

        $businesses->getCollection()->transform(
            fn (Business $business) => $this->businessResponse($business)
        );

        return response()->json([
            'data' => $businesses,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Business Details
    |--------------------------------------------------------------------------
    */

    public function business(Business $business): JsonResponse
    {
        abort_unless(
            $business->is_active,
            404
        );

        $business->load('businessType');

        return response()->json([
            'data' => $this->businessResponse($business),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Business Details From Body
    |--------------------------------------------------------------------------
    */

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
            ->with('businessType')
            ->where('id', $validated['business_id'])
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'data' => $this->businessResponse($business),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    public function categories(Business $business): JsonResponse
    {
        abort_unless(
            $business->is_active,
            404
        );

        $categories = $business->categories()
            ->with([
                'children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
            ])
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $categories
                ->map(fn (Category $category) => $this->categoryResponse($category))
                ->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Category Products
    |--------------------------------------------------------------------------
    */

    public function categoryProducts(
        Business $business,
        Category $category
    ): JsonResponse {
        abort_unless(
            $business->is_active &&
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
                'business',
                'category',
                'modifierGroups' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderByDesc('is_required')
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'modifierGroups.options' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
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
                                ->where('category_id', $subCategory->id)
                                ->values()
                                ->map(fn (Product $product) =>
                                    $this->productResponse($product)
                                )
                                ->values(),
                        ];
                    })
                    ->values(),

                'products' => $products
                    ->where('category_id', $category->id)
                    ->values()
                    ->map(fn (Product $product) =>
                        $this->productResponse($product)
                    )
                    ->values(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Categories From Body
    |--------------------------------------------------------------------------
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

        $business = Business::query()
            ->findOrFail($validated['business_id']);

        return $this->categories($business);
    }

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    public function products(
        Request $request,
        Business $business
    ): JsonResponse {
        abort_unless(
            $business->is_active,
            404
        );

        $query = Product::query()
            ->with([
                'business',
                'category',

                'modifierGroups' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderByDesc('is_required')
                    ->orderBy('sort_order')
                    ->orderBy('id'),

                'modifierGroups.options' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
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
                    $category->children()
                        ->where('is_active', true)
                        ->pluck('id')
                        ->all()
                );
            }

            $query->whereIn('category_id', $categoryIds);
        }

        $products = $query->paginate(
            min(
                $request->integer('per_page', 20),
                100
            )
        );

        $products->getCollection()->transform(
            fn (Product $product) => $this->productResponse($product)
        );

        return response()->json([
            'data' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Products From Body
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Popular Merchants
    |--------------------------------------------------------------------------
    */

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
            'data' => $merchants
                ->map(fn (Business $business) =>
                    $this->popularMerchantResponse($business)
                )
                ->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Top Banners
    |--------------------------------------------------------------------------
    */

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
            'data' => $banners
                ->map(fn (Banner $banner) =>
                    $this->bannerResponse($banner)
                )
                ->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Featured Merchants
    |--------------------------------------------------------------------------
    */

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
            'data' => $merchants
                ->map(fn (Business $business) =>
                    $this->featuredMerchantResponse($business)
                )
                ->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Details
    |--------------------------------------------------------------------------
    */

    public function product(Product $product): JsonResponse
    {
        abort_unless(
            $product->is_active &&
            $product->is_available,
            404
        );

        $product->load([
            'business',
            'category',

            'modifierGroups' => fn ($query) => $query
                ->where('is_active', true)
                ->orderByDesc('is_required')
                ->orderBy('sort_order')
                ->orderBy('id'),

            'modifierGroups.options' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        return response()->json([
            'data' => $this->productResponse($product),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Details From Body
    |--------------------------------------------------------------------------
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

        $product = Product::query()
            ->findOrFail($validated['product_id']);

        return $this->product($product);
    }

    /*
    |--------------------------------------------------------------------------
    | Response Mappers
    |--------------------------------------------------------------------------
    */

    private function businessTypeResponse(BusinessType $type): array
    {
        return [
            'id' => (int) $type->id,
            'name' => (string) ($type->name ?? ''),
            'slug' => (string) ($type->slug ?? ''),
            'description' => (string) ($type->description ?? ''),

            'icon' => (string) (
                $type->icon ?? 'heroicon-o-building-storefront'
            ),

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
        ];
    }

    private function businessResponse(Business $business): array
    {
        return [
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

            'business_type' => $business->businessType
                ? $this->businessTypeResponse($business->businessType)
                : [],

            'created_at' => $business->created_at?->toIso8601String() ?? '',
            'updated_at' => $business->updated_at?->toIso8601String() ?? '',
        ];
    }

    private function categoryResponse(Category $category): array
    {
        return [
            'id' => (int) $category->id,
            'name' => (string) ($category->name ?? ''),
            'slug' => (string) ($category->slug ?? ''),
            'description' => (string) ($category->description ?? ''),

            'image' => $this->imageUrl(
                $category->image,
                ''
            ),

            'is_active' => (bool) $category->is_active,
            'sort_order' => (int) ($category->sort_order ?? 0),

            'settings' => is_array($category->settings)
                ? $category->settings
                : [],

            'subcategories' => $category->children
                ->where('is_active', true)
                ->values()
                ->map(fn (Category $child) => [
                    'id' => (int) $child->id,
                    'name' => (string) ($child->name ?? ''),
                    'slug' => (string) ($child->slug ?? ''),
                    'description' => (string) ($child->description ?? ''),

                    'image' => $this->imageUrl(
                        $child->image,
                        ''
                    ),

                    'is_active' => (bool) $child->is_active,
                    'sort_order' => (int) ($child->sort_order ?? 0),

                    'settings' => is_array($child->settings)
                        ? $child->settings
                        : [],
                ])
                ->values(),
        ];
    }

    private function productResponse(Product $product): array
    {
        return [
            'id' => (int) $product->id,
            'business_id' => (int) ($product->business_id ?? 0),
            'category_id' => (int) ($product->category_id ?? 0),

            'name' => (string) ($product->name ?? ''),
            'slug' => (string) ($product->slug ?? ''),
            'sku' => (string) ($product->sku ?? ''),
            'description' => (string) ($product->description ?? ''),

            'price' => (float) ($product->price ?? 0),
            'sale_price' => $product->sale_price !== null
                ? (float) $product->sale_price
                : 0,

            'image' => $this->imageUrl(
                $product->image,
                $product->business
                    ? $this->merchantFallbackImage($product->business)
                    : ''
            ),

            'preparation_time_minutes' =>
                (int) ($product->preparation_time_minutes ?? 0),

            'is_available' => (bool) $product->is_available,
            'is_featured' => (bool) $product->is_featured,
            'is_active' => (bool) $product->is_active,

            'sort_order' => (int) ($product->sort_order ?? 0),

            'settings' => is_array($product->settings)
                ? $product->settings
                : [],

            'business' => $product->business
                ? $this->businessResponse($product->business)
                : [],

            'category' => $product->category
                ? $this->categoryResponseWithoutChildren($product->category)
                : [],

            'modifier_groups' => $product->relationLoaded('modifierGroups')
                ? $product->modifierGroups
                    ->map(fn ($group) => $this->modifierGroupResponse($group))
                    ->values()
                : [],

            'created_at' => $product->created_at?->toIso8601String() ?? '',
            'updated_at' => $product->updated_at?->toIso8601String() ?? '',
        ];
    }

    private function categoryResponseWithoutChildren(
        Category $category
    ): array {
        return [
            'id' => (int) $category->id,
            'business_id' => (int) ($category->business_id ?? 0),
            'parent_id' => (int) ($category->parent_id ?? 0),

            'name' => (string) ($category->name ?? ''),
            'slug' => (string) ($category->slug ?? ''),
            'description' => (string) ($category->description ?? ''),

            'image' => $this->imageUrl(
                $category->image,
                ''
            ),

            'is_active' => (bool) $category->is_active,
            'sort_order' => (int) ($category->sort_order ?? 0),

            'settings' => is_array($category->settings)
                ? $category->settings
                : [],

            'created_at' => $category->created_at?->toIso8601String() ?? '',
            'updated_at' => $category->updated_at?->toIso8601String() ?? '',
        ];
    }

    private function modifierGroupResponse($group): array
    {
        return [
            'id' => (int) $group->id,
            'business_id' => (int) ($group->business_id ?? 0),

            'name' => (string) ($group->name ?? ''),
            'slug' => (string) ($group->slug ?? ''),
            'description' => (string) ($group->description ?? ''),

            'is_required' => (bool) $group->is_required,

            'min_selections' => (int) ($group->min_selections ?? 0),
            'max_selections' => (int) ($group->max_selections ?? 0),

            'is_active' => (bool) $group->is_active,
            'sort_order' => (int) ($group->sort_order ?? 0),

            'options' => $group->relationLoaded('options')
                ? $group->options
                    ->map(fn ($option) => [
                        'id' => (int) $option->id,
                        'modifier_group_id' =>
                            (int) ($option->modifier_group_id ?? 0),

                        'name' => (string) ($option->name ?? ''),
                        'slug' => (string) ($option->slug ?? ''),

                        'price' => (float) ($option->price ?? 0),

                        'is_default' => (bool) $option->is_default,
                        'is_active' => (bool) $option->is_active,

                        'sort_order' => (int) ($option->sort_order ?? 0),

                        'created_at' =>
                            $option->created_at?->toIso8601String() ?? '',

                        'updated_at' =>
                            $option->updated_at?->toIso8601String() ?? '',
                    ])
                    ->values()
                : [],

            'created_at' => $group->created_at?->toIso8601String() ?? '',
            'updated_at' => $group->updated_at?->toIso8601String() ?? '',
        ];
    }

    private function popularMerchantResponse(Business $business): array
    {
        return [
            'id' => (int) $business->id,
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

            'city' => (string) ($business->city ?? ''),

            'business_type' => $business->businessType
                ? $this->businessTypeResponse($business->businessType)
                : [],

            'is_featured' => (bool) $business->is_featured,
        ];
    }

    private function bannerResponse(Banner $banner): array
    {
        return [
            'id' => (int) $banner->id,

            'title' => (string) ($banner->title ?? ''),
            'subtitle' => (string) ($banner->subtitle ?? ''),

            'image' => $this->imageUrl(
                $banner->image,
                ''
            ),

            'mobile_image' => $this->imageUrl(
                $banner->mobile_image,
                ''
            ),

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

                    'logo' => $this->imageUrl(
                        $banner->business->logo,
                        $this->merchantFallbackImage($banner->business)
                    ),
                ]
                : [],

            'sort_order' => (int) ($banner->sort_order ?? 0),
        ];
    }

    private function featuredMerchantResponse(
        Business $business
    ): array {
        return [
            'id' => (int) $business->id,

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
            'city' => (string) ($business->city ?? 'Tripoli'),

            'latitude' => (float) ($business->latitude ?? 34.4367),
            'longitude' => (float) ($business->longitude ?? 35.8497),

            'is_featured' => (bool) $business->is_featured,

            'category' => (string) (
                $business->businessType?->name ?? 'Restaurant'
            ),

            'eta' => '25–35 min',
            'fee' => 1.50,
            'minimum' => 5.00,

            'products' => $business->products
                ->map(fn (Product $product) => [
                    'id' => (int) $product->id,
                    'name' => (string) ($product->name ?? ''),
                    'description' => (string) ($product->description ?? ''),

                    'price' => (float) (
                        $product->sale_price
                            ?? $product->price
                            ?? 0
                    ),

                    'image' => $this->imageUrl(
                        $product->image,
                        $this->merchantFallbackImage($business)
                    ),

                    'customizable' => $product->relationLoaded('modifierGroups')
                        ? $product->modifierGroups->isNotEmpty()
                        : false,
                ])
                ->values(),

            'business_type' => $business->businessType
                ? $this->businessTypeResponse($business->businessType)
                : [],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function merchantFallbackImage(Business $business): string
    {
        return $business->businessType?->slug === 'coffee'
            ? 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=85'
            : 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=85';
    }

    private function imageUrl(
        ?string $image,
        string $fallback
    ): string {
        if (! $image) {
            return $fallback;
        }

        if (
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://')
        ) {
            return $image;
        }

        return asset(
            'storage/' . ltrim($image, '/')
        );
    }
}