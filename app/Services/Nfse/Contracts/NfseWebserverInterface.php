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
namespace App\Services\Nfse\Contracts;

use App\Models\Companies;
use Illuminate\Http\Request;

interface NfseWebserverInterface
{
    public function localiza(Request $request, Companies $company): mixed;  
}
