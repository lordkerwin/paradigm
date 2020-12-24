<?php

namespace App\Http\Transformers;

use App\Models\Product;

class ProductTransformer
{

    public static function toInstance(array $input, $product = null): ?Product
    {
        if (empty($product)) {
            $product = new Product();
        }

        foreach ($input as $key => $value) {
            switch ($key) {
                case 'name':
                    $product->name = $value;
                    break;
                case 'slug':
                    $product->slug = $value;
                    break;
                case 'active':
                    $product->active = $value;
                    break;
                case 'quantity':
                    $product->quantity = $value;
                    break;
                case 'reserve_quantity':
                    $product->reserve_quantity = $value;
                    break;
                case 'price':
                    $product->price = $value;
                    break;
                case 'weight_grams':
                    $product->weight_grams = $value;
                    break;
                case 'image':
                    $product->image = $value;
                    break;
            }
        }

        return $product;
    }
}

