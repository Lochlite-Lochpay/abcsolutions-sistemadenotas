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
use Illuminate\Support\Facades\Log;
use CloudDfe\SdkPHP\Nfse;
use App\Services\Accounting\AccountingService;

class IntegraNotas implements NfseWebserverInterface
{
    public function localiza(Request $request, Companies $company): mixed
    {
        try {
            $params = [
                "token" => $company->token,
                "ambiente" => 1, // 1 - Produção / 2 - Homologação
                "options" => [
                    "debug" => true,
                    "timeout" => 60,
                    "port" => 443,
                    "http_version" => CURL_HTTP_VERSION_NONE
                ]
            ];

            $nfse = new Nfse($params);

            $payload = [
                "nfse_numero_inicial" => $request->get('nfse_numero_inicial'),
                "nfse_numero_final" => $request->get('nfse_numero_final'),
                "rps_numero" => $request->get('rps_numero'),
                "rps_serie" => $request->get('rps_serie'),
                "rps_tipo" => $request->get('rps_tipo'),
                'data_emissao_inicial' => $request->get('data_emissao_inicial', now()->subDays(30)->format('Y-m-d')),
                'data_emissao_final' => $request->get('data_emissao_final', now()->format('Y-m-d')),
                'data_competencia_inicial' => $request->get('data_competencia_inicial', now()->subDays(30)->format('Y-m-d')),
                'data_competencia_final' => $request->get('data_competencia_final', now()->format('Y-m-d')),
                "tomador_cnpj" => $request->get('tomador_cnpj'),
                "tomador_cpf" => $request->get('tomador_cpf'),
                "tomador_im" => $request->get('tomador_im'),
                "intermediario_cnpj" => $request->get('intermediario_cnpj'),
                "intermediario_cpf" => $request->get('intermediario_cpf'),
                "intermediario_im" => $request->get('intermediario_im'),
                "nfse_numero" => $request->get('nfse_numero'),
                "pagina" => $request->get('pagina'),
            ];

            $resp = $nfse->localiza($payload);
            $notasArray = [];
            if (!empty($resp->notas)) {
                // Decodifica base64
                $gzData = base64_decode($resp->notas);

                // Salva temporariamente o arquivo
                $gzPath = storage_path('app/tmp_nfse.gz');
                file_put_contents($gzPath, $gzData);

                // Lê e descompacta o XML
                $xmlContent = file_get_contents("compress.zlib://$gzPath");

                // Faz o parse
                $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);

                // Define o namespace
                $xml->registerXPathNamespace('ns1', 'http://www.abrasf.org.br/nfse.xsd');

                // Captura os <CompNfse>
                $compNfses = $xml->xpath('//ns1:ListaNfse/ns1:CompNfse');

                // Converte para array
                foreach ($compNfses as $comp) {
                    $notasArray[] = json_decode(json_encode($comp), true);
                }

                // Remove arquivo temporário
                unlink($gzPath);
            }

            // Verifica se o serviço de contabilidade está ativo
            if($company->accounting == true || $company->accounting == 1 || $company->accounting == 'true') {
                // Envia os dados para o serviço de contabilidade
                $accounting = new AccountingService($company, 'Nfse', 'Onvio');
                $accounting->processInvoice($notasArray);
            }

            // Retorna dados organizados
            return [
                'success' => $resp->sucesso ?? false,
                'code' => $resp->codigo ?? null,
                'message' => $resp->mensagem ?? null,
                'notas' => $resp->notas ?? null,
                'notasarray' => $notasArray,
            ];
        } catch (Exception $e) {
            Log::error('Erro no IntegraNotasService: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage() ?? 'Erro ao buscar NFS-e.',
            ];
        }
    }
}
