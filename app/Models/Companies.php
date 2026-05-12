<?php

/****** Another website produced by The Lochlite & Lochpay Company
___
|   |
|   |             _______         ______       __      __
|   |            /       \       /  ____|     |  |    |  |
|   |______     /         \     /  /          |  |----|  |
|          |    \         /     \  \____      |  |----|  |
|__________|     \_______/       \______|     |__|    |__| Lite ®


Long live Lochlite! ******/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    protected $fillable = [
        'user_id',
        'cnpj',
        'name',
        'token',
        'token_qive',
        'api_id',
        'api_key',
        'client_id_accountings',
        'client_key_accountings',
        'client_audience_accountings',
        'access_token_accountings',
        'expire_token_accountings',
        'personal_integration_id_accountings',
        'api_generate_integration_id_accountings',
        'accounting',
    ];
}
