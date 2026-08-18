# Flutter catalog API integration

The original `GET` catalog APIs remain active. The following compatibility
routes were added because the Flutter networking layer sends identifiers in a
JSON request body.

Base URL: `https://api.talabetna-go.com`

## 1. Merchant categories

`POST /api/v1/catalog/categories`

```json
{
  "business_id": 1
}
```

The response contains main categories. Each main category contains its direct
`products` and its `children`; each child category also contains `products`.

## 2. Merchant/category products

`POST /api/v1/catalog/products`

```json
{
  "business_id": 1,
  "category_id": 3,
  "per_page": 20
}
```

`category_id` is optional. When it identifies a main category, the response
includes products assigned directly to it and products in its subcategories.

## 3. Product details

`POST /api/v1/catalog/product-details`

```json
{
  "product_id": 7
}
```

## Flutter/Dio example

```dart
await dio.post(
  '/api/v1/catalog/categories',
  data: {'business_id': businessId},
);

await dio.post(
  '/api/v1/catalog/products',
  data: {
    'business_id': businessId,
    'category_id': categoryId,
  },
);

await dio.post(
  '/api/v1/catalog/product-details',
  data: {'product_id': productId},
);
```

All IDs must be sent as integers. Invalid or missing IDs return HTTP `422`.
