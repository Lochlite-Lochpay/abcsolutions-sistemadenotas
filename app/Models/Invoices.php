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

class Invoices extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'number',
        'service_provider',
        'service_provider_document',
        'service_taker',
        'service_taker_document',
        'accountings',
        'accountings_response',
        'sending_id_accountings',
    ];

    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }

    public function scopeFilterByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
