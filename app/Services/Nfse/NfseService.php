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

namespace App\Services\Nfse;

use App\Exceptions\UnsupportedWebserverException;
use App\Models\Companies;
use App\Services\Nfse\Contracts\NfseWebserverInterface;
use App\Services\Nfse\Webservers\Qive;

class NfseService
{
    protected Companies $company;

    protected NfseWebserverInterface $webserver;

    public function __construct(Companies $company, ?string $webserverName = 'Qive')
    {
        // Se não houver empresa, pega a primeira empresa do usuário
        if (! $company) {
            $this->company = Companies::where('user_id', auth()->user()->id)->first();
        } else {
            $this->company = $company;
        }

        if (! $webserverName) {
            throw new \Exception('Nenhum webserver de NFS-e padrão configurado.');
        }
        $this->webserver = $this->resolveWebserver($webserverName);
    }

    protected function resolveWebserver(string $name): NfseWebserverInterface
    {
        return match ($name) {
            'Qive' => new Qive,
            default => throw new UnsupportedWebserverException($name),
        };
    }

    public function localiza($request, bool $processAccounting = true)
    {
        return $this->webserver->localiza($request, $this->company, $processAccounting);
    }

    public function convert($xmlstring)
    {
        $ini = new NfseXmlVersionConversion;

        return $ini->convert($xmlstring);
    }
}
