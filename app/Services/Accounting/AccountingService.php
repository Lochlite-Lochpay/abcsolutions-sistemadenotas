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
namespace App\Services\Accounting;

use App\Exceptions\UnsupportedWebserverException;
use App\Models\Companies;
use App\Models\Invoices;
use App\Services\Accounting\Contracts\AccountingWebserverInterface;
use App\Services\Accounting\Webserver\Onvio;
use Illuminate\Support\Facades\Log;

class AccountingService
{
    protected Companies $company;
    protected AccountingWebserverInterface $webserver;
    protected string $invoiceType;

    public function __construct(Companies $company, ?string $invoiceType = "nfse", ?string $webserverName = "Onvio")
    {
        // Se não houver empresa, pega a primeira empresa do usuário        
        if (!$company) {
            $this->company = Companies::where('user_id', auth()->user()->id)->first();
        } else {
            $this->company = $company;
        }

        if (!$invoiceType) {
            throw new \Exception("Nenhum tipo de nota fiscal padrão configurado.");
        }

        $this->invoiceType = $invoiceType;
    
        if (!$webserverName) {
            throw new \Exception("Nenhum webserver de contabilidade padrão configurado.");
        }

        $this->webserver = $this->resolveWebserver($webserverName);
    }

    protected function resolveWebserver(string $name): AccountingWebserverInterface
    {
        return match ($name) {
            'Onvio' => new Onvio($this->company, $this->invoiceType),
            default => throw new UnsupportedWebserverException($name),
        };
    }

    public function processInvoice(array $invoices): void
    {

       foreach ($invoices as $item)
       {
            $number = $item['Nfse']['InfNfse']['Numero'];
            if (empty($number)) {
                // Se não houver número, ignora este item
                continue;
            }
             
            try {
            // Adiciona campos de prestador e tomador
            $serviceProvider = $item['Nfse']['InfNfse']['PrestadorServico']['RazaoSocial'] ?? null;
            $serviceProviderDocument = $item['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Prestador']['CpfCnpj']['Cnpj']
                ?? $item['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Prestador']['CpfCnpj']['Cpf']
                ?? null;
            $serviceTaker = $item['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['RazaoSocial'] ?? null;
            $serviceTakerDocument = $item['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['IdentificacaoTomador']['CpfCnpj']['Cpf'] 
                ?? $item['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['IdentificacaoTomador']['CpfCnpj']['Cnpj'] 
                ?? null;
            } catch (\Exception $e) {
                // Tratar exceções
                //dd($e);
                Log::error("Erro ao processar nota fiscal: {$e->getMessage()}");
            }

            if(Invoices::where('number', $number)->exists()) {
                $invoice = Invoices::where('number', $number)->first();
                if($invoice->accountings == false || $invoice->accountings == 0){
                  $conv = $this->convert($item);
                  $this->webserver->send(Invoices::where('number', $number)->first(), $conv['xml']);
                }
                // Se a nota já existe, não processa novamente
                continue;
            } else {
                // Cria a nota fiscal no banco de dados
                $invoice = Invoices::create([
                    'company_id' => $this->company->id,
                    'number' => $number,
                    'type' => $this->invoiceType,
                    'service_provider' => $serviceProvider ?? null,
                    'service_provider_document' => $serviceProviderDocument ?? null,
                    'service_taker' => $serviceTaker ?? null,
                    'service_taker_document' => $serviceTakerDocument ?? null,
                    'accountings' => false,
                ]);
                $conv = $this->convert($item);
                $this->webserver->send($invoice, $conv['xml']);
            }           
       }   
    }

    public function convert(array $invoice)
    {
        return $this->webserver->convert($invoice);
    }
    
    public function checkSend($id): mixed
    {
        return $this->webserver->checkSend($id);
    }
}

