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

use App\Models\Invoices;

interface AccountingWebserverInterface
{
    public function convert(array $invoice): mixed;

    public function send(Invoices $invoice, string $xml): mixed;
}
