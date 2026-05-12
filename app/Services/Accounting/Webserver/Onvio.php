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
namespace App\Services\Accounting\Webserver;

use App\Services\Accounting\Contracts\AccountingWebserverInterface;
use App\Services\Accounting\Contracts\AccountingOnvioInterface;
use App\Services\Accounting\Invoice\Onvio\NfseInvoice;
use Illuminate\Http\Request;
use App\Models\Companies;
use Exception;
use App\Exceptions\UnsupportedWebserverException;
use App\Models\Invoices;
use Dom\XMLDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Onvio implements AccountingWebserverInterface
{
    protected Companies $company;
    protected AccountingOnvioInterface $invoiceType;

    public function __construct(Companies $company, ?string $invoiceType = "Nfse")
    {
        $this->company = $company;
        
        if (!$invoiceType) {
            throw new \Exception("Nenhum tipo de nota fiscal padrão configurado.");
        }
        // Resolve o tipo de nota fiscal
        $this->invoiceType = $this->resolveInvoiceType($invoiceType);
    }

    protected function resolveInvoiceType(string $name): AccountingOnvioInterface
    {
        return match ($name) {
            'Nfse' => new NfseInvoice(),
            default => throw new UnsupportedWebserverException($name),
        };
    }

    public function verifyToken(): bool
    {
        // Verifica se o token está presente e se não expirou
        if (empty($this->company->access_token_accountings) || empty($this->company->expire_token_accountings)) {
            return false;
        }

        // Verifica se o token expirou
        if (now()->greaterThanOrEqualTo($this->company->expire_token_accountings)) {
            return false;
        }

        return true;
    }

    public function getToken(): mixed
    {
        try {
            // Verifica se a empresa possui as credenciais necessárias
            if (empty($this->company->client_id_accountings) || empty($this->company->client_key_accountings) || empty($this->company->client_audience_accountings)) {
                throw new Exception('Credenciais de autenticação não configuradas para a empresa.');
            }
            $username = $this->company->client_id_accountings;
            $password = $this->company->client_key_accountings;
            $audience = $this->company->client_audience_accountings;

            $response = Http::withBasicAuth($username, $password)
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cookie' => 'did=s%3Av0%3A145b8a90-ea57-11eb-ae8a-877f15a4a518.QhUcTCGsMP28yWAB%2BYsUUZ5Gw4Srxf%2F0IDRkKPUQQHs; did_compat=s%3Av0%3A145b8a90-ea57-11eb-ae8a-877f15a4a518.QhUcTCGsMP28yWAB%2BYsUUZ5Gw4Srxf%2F0IDRkKPUQQHs',
            ])
            ->asForm()
            ->post('https://auth.thomsonreuters.com/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $username,
                'client_secret' => $password,
                'audience' => $audience,
            ]);

            if ($response->failed()) {
                Log::error('Erro ao obter token: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'Erro ao obter token de autenticação.',
                ];
            }

            $tokenData = $response->json();
            if (empty($tokenData['access_token'])) {
                Log::error('Token de acesso não encontrado na resposta: ' . json_encode($tokenData));
                return [
                    'success' => false,
                    'message' => 'Token de acesso não encontrado na resposta.',
                ];
            }

            $this->company->access_token_accountings = $tokenData['access_token'];
            $this->company->expire_token_accountings = now()->addSeconds($tokenData['expires_in']); 
            $this->company->save();

            return $tokenData;
        } catch (Exception $e) {
            Log::error('Erro ao gerar o token da contabilidade: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao gerar o token da contabilidade.',     
            ];
        }
    }

    /**
     * Verifica a existência do ID de integração da contabilidade.
     *
     * @return mixed
     */
    public function verifyIntegrationId(): bool
    {
        // Verifica se o ID de integração está presente
        if (empty($this->company->api_generate_integration_id_accountings)) {
            return false;
        }

        // Verifica se o ID de integração não expirou
        if (now()->greaterThanOrEqualTo($this->company->expire_token_accountings)) {
            return false;
        }

        return true;
    }

    /**
     * Obtém o ID de integração da contabilidade.
     *
     * @return mixed
     */
    public function getIntegrationId(): mixed
    {
        try {
            // Verifica se a empresa possui as credenciais necessárias
            if (empty($this->company->personal_integration_id_accountings)) {
                throw new Exception('ID de integração do contador não configurado para a empresa.');
            }
            
            $personalIntegrationId = $this->company->personal_integration_id_accountings; 
            $token = $this->company->access_token_accountings;                                        

            $response = Http::withToken($token)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'x-integration-key' => $personalIntegrationId,
            ])
            ->post('https://api.onvio.com.br/dominio/integration/v1/activation/enable');

            if ($response->failed()) {
                Log::error('Erro ao obter integrationKey: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'Erro ao obter integrationKey.',
                ];
            }

            $integrationKeyData = $response->json();
            if (empty($integrationKeyData['integrationKey'])) {
                Log::error('IntegrationKey não encontrado na resposta: ' . json_encode($integrationKeyData));
                return [
                    'success' => false,
                    'message' => 'IntegrationKey não encontrado na resposta.',
                ];
            }

            $this->company->api_generate_integration_id_accountings = $integrationKeyData['integrationKey'];
            $this->company->save();

            return $integrationKeyData;
        } catch (Exception $e) {
            Log::error('Erro ao gerar o integrationKey da contabilidade: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao gerar o integrationKey da contabilidade.',
            ];
        }
    }

    public function convert(array $notasArray): mixed
    {
        return $this->invoiceType->convert($notasArray);
    }

    public function send(Invoices $invoice, $xml): mixed
    {
        if (!$this->verifyToken()) {
            $tokenResponse = $this->getToken();
        }
        if( !$this->verifyIntegrationId()) {
            $integrationIdResponse = $this->getIntegrationId();
        }
        return $this->invoiceType->send($this->company, $invoice, $xml);
    }
    
    public function checkSend($id): mixed
    {
        $client = new \GuzzleHttp\Client();
	    $headers = [
	      'x-integration-key' => $this->company->api_generate_integration_id_accountings,
	      'Authorization' => 'Bearer '.$this->company->access_token_accountings
	    ];
	    $request = new \GuzzleHttp\Psr7\Request('GET', 'https://api.onvio.com.br/dominio/invoice/v3/batches/'.$id, $headers);
     	$res = $client->sendAsync($request)->wait();
	    return $res->getBody()->getContents();
	/*exemplo de retorno
	{
		"type": "batchV2Dto",
		"lastStatusOn": "2024-06-26T18:28:47.779937",
		"apiVersion": "v2",
		"boxeFile": true,
		"filesExpanded": [
			{
				"lastApiStatusOn": "2024-06-26T18:28:47.878257",
				"lastBoxeStatusOn": "2024-06-26T18:28:47.878258",
				"apiStatus": {
					"code": "SA2",
					"message": "Arquivo armazenado na API."
				},
				"boxeStatus": {
					"code": "SB3",
					"message": "Aguardando envio. Por favor aguarde o arquivo ser enviado ao BOX-e."
				}
			}
		],
		"id": "0A15110539BC4F3EAC147E61AE3EF398",
		"status": {
			"code": "S2",
			"message": "Processado."
		}
	}*/
    }
}
