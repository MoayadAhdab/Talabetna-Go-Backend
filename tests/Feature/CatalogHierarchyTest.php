<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CatalogHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_returns_direct_and_subcategory_products(): void
    {
        $merchant = $this->merchant('merchant-one');
        $main = $this->category($merchant, 'Meals');
        $sub = $this->category($merchant, 'Burgers', $main);

        $directProduct = $this->product($merchant, $main, 'Daily Dish');
        $nestedProduct = $this->product($merchant, $sub, 'Cheese Burger');

        $this->getJson("/api/v1/businesses/{$merchant->id}/categories")
            ->assertOk()
            ->assertJsonPath('data.0.id', $main->id)
            ->assertJsonPath('data.0.products.0.id', $directProduct->id)
            ->assertJsonPath('data.0.children.0.id', $sub->id)
            ->assertJsonPath('data.0.children.0.products.0.id', $nestedProduct->id);

        $this->getJson("/api/v1/businesses/{$merchant->id}/products?category_id={$main->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data.data');
    }

    public function test_category_and_product_must_belong_to_the_same_merchant(): void
    {
        $firstMerchant = $this->merchant('first-merchant');
        $secondMerchant = $this->merchant('second-merchant');
        $foreignCategory = $this->category($secondMerchant, 'Foreign');

        $this->expectException(ValidationException::class);

        $this->product($firstMerchant, $foreignCategory, 'Invalid Product');
    }

    private function merchant(string $slug): Business
    {
        $type = BusinessType::query()->firstOrCreate(
            ['slug' => 'restaurant'],
            ['name' => 'Restaurant']
        );

        return Business::query()->create([
            'business_type_id' => $type->id,
            'name' => str($slug)->headline(),
            'slug' => $slug,
        ]);
    }

    private function category(
        Business $merchant,
        string $name,
        ?Category $parent = null
    ): Category {
        return Category::query()->create([
            'business_id' => $merchant->id,
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => str($name)->slug(),
        ]);
    }

    private function product(
        Business $merchant,
        Category $category,
        string $name
    ): Product {
        return Product::query()->create([
            'business_id' => $merchant->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => str($name)->slug(),
            'price' => 10,
        ]);
    }
}
