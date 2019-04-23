<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'yandex_direct_login',
        'yandex_direct_token',
        'yandex_direct_token_expire',
        'yandex_direct_balance',
        'yandex_market_login',
        'yandex_makret_token',
        'yandex_market_token_expire',
        'yandex_market_balance'
    ];
}