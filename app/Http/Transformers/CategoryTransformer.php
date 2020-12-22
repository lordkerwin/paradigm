<?php

namespace App\Http\Transformers;

use App\Models\Category;

class CategoryTransformer
{

    public static function toInstance(array $input, $category = null)
    {
        if (empty($category)) {
            $category = new Category();
        }

        foreach ($input as $key => $value) {
            switch ($key) {
                case 'name':
                    $category->name = $value;
                    break;
                case 'slug':
                    $category->slug = $value;
                    break;
            }
        }

        return $category;
    }
}
