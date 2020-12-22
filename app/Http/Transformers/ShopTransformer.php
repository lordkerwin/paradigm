<?php

namespace App\Http\Transformers;

use App\Models\Shop;

class ShopTransformer
{

    public static function toInstance(array $input, $shop = null)
    {
        if (empty($shop)) {
            $shop = new Shop();
        }

        foreach ($input as $key => $value) {
            switch ($key) {
                case 'name':
                    $shop->name = $value;
                    break;
                case 'domain':
                    $shop->domain = $value;
                    break;
                case 'active':
                    $shop->active = $value;
                    break;
            }
        }

        return $shop;
    }
}
