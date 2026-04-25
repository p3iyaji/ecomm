<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class ShopCache
{
    public static function bumpProductListVersion(): void
    {
        $v = (int) Cache::get('shop:product_list_version', 0);
        Cache::forever('shop:product_list_version', $v + 1);
    }

    public static function productListCacheKey(string $fullUrl): string
    {
        $v = (int) Cache::get('shop:product_list_version', 0);

        return 'products:list:'.$v.':'.md5($fullUrl);
    }

    public static function forgetProductDetail(string $slug): void
    {
        Cache::forget("product:{$slug}");
        Cache::forget("product:{$slug}:related");
    }
}
