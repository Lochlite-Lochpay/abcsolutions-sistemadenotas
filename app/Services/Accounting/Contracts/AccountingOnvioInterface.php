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

namespace App\Services\Accounting\Contracts;

use App\Models\Companies;
use App\Models\Invoices;

interface AccountingOnvioInterface
{
    public function convert(array $notasArray): mixed;

    public function send(Companies $company, Invoices $invoice, string $xml): mixed;
}
