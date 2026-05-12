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

namespace App\Services\Accounting\Invoice\Onvio;

use App\Models\Companies;
use App\Models\Invoices;
use App\Services\Accounting\Contracts\AccountingOnvioInterface;
use App\Services\Accounting\Converters\NfseXmlVersionConversion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NfseInvoice implements AccountingOnvioInterface
{
    /**
     * Converte a versão do XML da NFS-e para 1.0 em conformidade com o padrão ABRASF.
     *
     * @throws Exception
     */
    public function convert(array $notasArray): mixed
    {
        try {
            $converter = new NfseXmlVersionConversion;

            return $converter->convert($notasArray);
        } catch (Exception $e) {
            Log::error('Erro ao converter XML: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Erro ao converter XML.',
            ];
        }
    }

    /**
     * Envia o xml da NFS-e para a API.
     *
     * @param  Request  $request
     */
    public function send(Companies $company, Invoices $invoice, string $xml): mixed
    {
        try {
            // Verifica se a empresa possui as credenciais necessárias
            if (empty($company->client_id_accountings) || empty($company->client_key_accountings) || empty($company->client_audience_accountings)) {
                throw new Exception('Credenciais de autenticação não configuradas para a empresa.');
            }
            $accessToken = $company->access_token_accountings;
            $apiIntegrationKey = $company->api_generate_integration_id_accountings;
            // Remove espaços, aspas e outros caracteres indesejaveis decorrentes do armazenamento do xml como string
            $code = html_entity_decode(preg_replace('/[^\x20-\x7E]/', '', trim($xml, "\"'")));

            // Salva o arquivo xml
            $fileName = 'nfse_'.$invoice->number.'.xml'; // Nome do arquivo com prefixo nfse_ seguido do número da nota
            $path = 'invoices/company_'.$invoice->company_id.'/'.$fileName; // Caminho onde será salvo
            // Salvar o arquivo no storage
            Storage::disk('local')->put($path, $code);
            // Obter o caminho completo do arquivo salvo
            $fullPath = Storage::path($path);

            // FUnção fornecida pelo provedor da API Onvio
            $api = $this->envioXML($apiIntegrationKey, $accessToken, $fullPath);
            $status = $api['status'];
            $data = json_decode($api['body'], true);
            // dd($data);
            $invoice->accountings_response = json_encode($data, true);

            if (! in_array($status, [200, 201, 202])) { // Verifica se o status não está na lista de códigos de sucesso
                $invoice->accountings = false;
                $invoice->save();
                Log::error('Erro ao enviar XML: '.json_encode($data));

                return [
                    'success' => false,
                    'message' => 'Erro ao enviar XML.',
                ];
            }

            $invoice->sending_id_accountings = $data['id'] ?? null; // Obtém o ID do envio, se disponível
            $invoice->accountings = true;
            $invoice->save();

            return $data;
        } catch (Exception $e) {
            // dd($e);
            Log::error('Erro ao enviar xml: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Erro ao enviar xml.',
            ];
        }
    }

    public function envioXML($chave_integracao, $token, $xml_arquivo)
    {
        $client = new \GuzzleHttp\Client;
        $headers = [
            'x-integration-key' => $chave_integracao,
            'Authorization' => 'Bearer '.$token,
        ];
        $options = [
            'multipart' => [
                [
                    'name' => 'file[]',
                    'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($xml_arquivo, 'r'),
                    'filename' => $xml_arquivo,
                    'headers' => [
                        'Content-Type' => 'application/xml',
                    ],
                ],
                [
                    'name' => 'query',
                    'contents' => '{"boxeFile":true}',
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ],
            ],
        ];
        $request = new \GuzzleHttp\Psr7\Request('POST', 'https://api.onvio.com.br/dominio/invoice/v3/batches', $headers);
        $res = $client->sendAsync($request, $options)->wait();

        return [
            'status' => $res->getStatusCode(),
            'body' => $res->getBody()->getContents(),
        ];
        /*exemplo de retorno
        {
            "type": "batchV2Dto",
            "lastStatusOn": "2024-06-26T18:28:47.722974",
            "apiVersion": "v2",
            "boxeFile": true,
            "filesExpanded": [],
            "id": "0A15110539BC4F3EAC147E61AE3EF398",
            "status": {
                "code": "S1",
                "message": "Aguardando processamento. Por favor aguarde o arquivo ser processado."
            }
        }
        */
    }
}
