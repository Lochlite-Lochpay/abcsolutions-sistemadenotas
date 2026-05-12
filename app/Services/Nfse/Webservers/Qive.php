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
namespace App\Services\Nfse\Webservers;

use App\Services\Nfse\Contracts\NfseWebserverInterface;
use Illuminate\Http\Request;
use App\Models\Companies;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Accounting\AccountingService;

class Qive implements NfseWebserverInterface
{
    public function localiza(Request $request, Companies $company): mixed
    {
        try {

            $response = Http::withHeaders([
                'X-API-ID' => $company->api_id,
                'X-API-KEY' => $company->api_key,
                'Content-Type' => 'application/json',
            ])->get('https://api.arquivei.com.br/v1/nfse/received', [
                "cnpj" => $request->get('document'),
            ]);

            $resp = json_decode($response->body());
            $notasArray = [];

            if (!empty($resp->data)) {
                foreach ($resp->data as $nota) {
                    try {
                        $xmlContent = base64_decode($nota->xml);

                        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);

                        if ($xml) {
                            $notaArray = json_decode(json_encode($xml), true);
                            $notasArray[] = $notaArray;
                        } else {
                            Log::warning('Não foi possível parsear o XML da nota ID: ' . ($nota->id ?? 'sem id'));
                        }
                    } catch (Exception $e) {
                        Log::error('Erro ao processar nota: ' . $e->getMessage());
                    }
                }
            }

            // Verifica se o serviço de contabilidade está ativo
            if($company->accounting == true || $company->accounting == 1 || $company->accounting == 'true') {
                // Envia os dados para o serviço de contabilidade
                $accounting = new AccountingService($company, 'Nfse', 'Onvio');
                $accounting->processInvoice($notasArray);
            }
                        
            // Retorna dados organizados
            return [
                'success' => $resp->status->code == 200 ? true : false,
                'code' => $resp->status->code ?? null,
                'message' => $resp->status->message ?? null,
                'notas' => $resp->data ?? [],
                'notasarray' => $notasArray,
                'page' => $resp->page ?? [],
                'count' => $resp->count ?? 0, 
                'signature' => $resp->signature ?? null, 
            ];
        } catch (Exception $e) {
            Log::error('Erro no QiveService: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage() ?? 'Erro ao buscar NFS-e.',
            ];
        }
    }
}
