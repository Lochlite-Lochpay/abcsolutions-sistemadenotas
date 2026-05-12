<?php

/****** Another website produced by The Lochlite & Lochpay Company
___
|   |
|   |             _______         ______       __      __
|   |            /       \       /  ____|     |  |    |  |
|   |______     /         \     /  /          |  |----|  |
|          |    \         /     \  \____      |  |----|  |
|__________|     \_______/       \______|     |__|    |__| Lite Â®


Long live Lochlite! ******/

namespace App\Services\Nfse\Webservers;

use App\Models\Companies;
use App\Services\Accounting\AccountingService;
use App\Services\Nfse\Contracts\NfseWebserverInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Qive implements NfseWebserverInterface
{
    protected const ENDPOINT = 'https://api.arquivei.com.br/v1/dfe/nfse';

    protected function normalizeDigits(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    protected function requestedRole(Request $request): string
    {
        return $request->get('webserver') === 'tomadas' ? 'taker' : 'emitter';
    }

    protected function ownerRoles(Request $request): string
    {
        return $this->requestedRole($request);
    }

    protected function buildFilter(Request $request): array
    {
        $filter = [
            'ownerRoles' => $this->ownerRoles($request),
        ];

        $emissionDate = array_filter([
            'From' => $request->get('data_emissao_inicial'),
            'To' => $request->get('data_emissao_final'),
        ], static fn ($value) => filled($value));

        if (! empty($emissionDate)) {
            $filter['emissionDate'] = $emissionDate;
            $filter['createdAt'] = $emissionDate;
        }

        $competence = array_filter([
            'From' => $request->get('data_competencia_inicial'),
            'To' => $request->get('data_competencia_final'),
        ], static fn ($value) => filled($value));

        if (! empty($competence)) {
            $filter['competence'] = $competence;
        }

        if ($request->filled('document')) {
            $document = $this->normalizeDigits($request->get('document'));

            if ($document) {
                $filter[$this->requestedRole($request) === 'taker' ? 'takerCnpj' : 'emitterCnpj'] = [$document];
                $filter['owners'] = [$document];
            }
        }

        if ($request->filled('tomador_cnpj')) {
            $filter['takerCnpj'] = array_values(array_filter([
                $this->normalizeDigits($request->get('tomador_cnpj')),
                isset($filter['takerCnpj']) ? current($filter['takerCnpj']) : null,
            ]));
        }

        if ($request->filled('tomador_cpf')) {
            $cpf = $this->normalizeDigits($request->get('tomador_cpf'));
            if ($cpf) {
                $filter['takerCnpj'] = array_values(array_filter([
                    ...($filter['takerCnpj'] ?? []),
                    $cpf,
                ]));
            }
        }

        if ($request->filled('is_canceled')) {
            $filter['isCanceled'] = filter_var($request->get('is_canceled'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return $filter;
    }

    protected function buildProjection(): array
    {
        return [
            'fields' => [
                'xmlAbrasf',
                'xmlAbrasfRtc',
                'originalXml',
                'origin',
                'flagErp',
                'jsonDocument',
                'emitter',
                'taker',
                'status',
                'event',
            ],
        ];
    }

    protected function buildPaginator(Request $request): ?string
    {
        if (! $request->filled('pagina')) {
            return null;
        }

        return base64_encode(json_encode([
            'page' => (int) $request->get('pagina'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected function extractXmlString(mixed $xmlDocument): ?string
    {
        if (empty($xmlDocument)) {
            return null;
        }

        if (! is_string($xmlDocument)) {
            return null;
        }

        $decoded = base64_decode($xmlDocument, true);

        return $decoded === false ? $xmlDocument : $decoded;
    }

    protected function normalizeTagName(string $tag): string
    {
        $tag = preg_replace('/^[^:]+:/', '', $tag);
        $tag = html_entity_decode($tag, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $tag = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $tag) ?: $tag;
        $tag = preg_replace('/[^a-zA-Z0-9]+/', '', $tag);

        return strtolower($tag);
    }

    protected function normalizeXmlNode(mixed $value): mixed
    {
        if (is_object($value)) {
            $value = json_decode(json_encode($value), true);
        }

        if (! is_array($value)) {
            return $value;
        }

        $normalized = [];

        foreach ($value as $key => $item) {
            $normalizedKey = is_string($key) ? $this->normalizeTagName($key) : $key;
            $normalized[$normalizedKey] = $this->normalizeXmlNode($item);
        }

        return $normalized;
    }

    protected function findNodeByAliases(mixed $tree, array $aliases): mixed
    {
        if (! is_array($tree)) {
            return null;
        }

        $normalizedAliases = array_map([$this, 'normalizeTagName'], $aliases);

        foreach ($tree as $key => $value) {
            if (is_string($key) && in_array($this->normalizeTagName($key), $normalizedAliases, true)) {
                return $value;
            }

            if (is_array($value)) {
                $found = $this->findNodeByAliases($value, $aliases);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    protected function findValueByAliases(mixed $tree, array $aliases): mixed
    {
        $node = $this->findNodeByAliases($tree, $aliases);

        if ($node === null) {
            return null;
        }

        if (is_array($node)) {
            foreach (['value', 'text', '#text', '0'] as $candidate) {
                if (array_key_exists($candidate, $node) && ! is_array($node[$candidate])) {
                    return $node[$candidate];
                }
            }

            if (count($node) === 1) {
                return reset($node);
            }
        }

        return $node;
    }

    protected function normalizeXmlToArray(mixed $xmlDocument): ?array
    {
        if (empty($xmlDocument)) {
            return null;
        }

        if (is_object($xmlDocument)) {
            $xmlDocument = json_decode(json_encode($xmlDocument), true);
        }

        if (is_array($xmlDocument)) {
            return $this->normalizeXmlNode($xmlDocument);
        }

        if (! is_string($xmlDocument)) {
            return null;
        }

        $decoded = $this->extractXmlString($xmlDocument);

        $xml = simplexml_load_string($decoded, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (! $xml) {
            return null;
        }

        return $this->normalizeXmlNode(json_decode(json_encode($xml), true));
    }

    protected function buildCanonicalNfseDocument(array $document): array
    {
        $root = $this->findNodeByAliases($document, ['nfse', 'compnfse', 'nfs-e', 'nfsecomp', 'nfseps', 'notafiscal']);
        if (! is_array($root)) {
            $root = $document;
        }

        $inf = $this->findNodeByAliases($root, ['infnfse', 'infnfsse', 'infnfs', 'nfse']) ?? $root;
        if (! is_array($inf)) {
            $inf = $root;
        }

        $prestador = $this->findNodeByAliases($root, ['prestadorservico', 'prestador', 'emitente', 'emitter']) ?? [];
        $taker = $this->findNodeByAliases($root, ['tomadorservico', 'tomador', 'taker', 'recebedor', 'receiver']) ?? [];
        $servico = $this->findNodeByAliases($inf, ['servico', 'service']) ?? [];
        $valores = $this->findNodeByAliases($inf, ['valoresnfse', 'valores', 'valor']) ?? [];

        $numero = $this->findValueByAliases($inf, ['numero', 'numeronfse', 'nfsenumero', 'nfse', 'id']);
        $dataEmissao = $this->findValueByAliases($inf, ['dataemissao', 'emissao', 'emissiondate', 'createdat']);
        $codigoVerificacao = $this->findValueByAliases($inf, ['codigoverificacao', 'verificationcode', 'codigoverif']);
        $descricaoServico = $this->findValueByAliases($servico, ['discriminacao', 'descricao', 'description']);
        $codigoTributacao = $this->findValueByAliases($servico, ['codigotributacaomunicipio', 'codigotributacao', 'tributationcode']);
        $exigibilidadeIss = $this->findValueByAliases($servico, ['exigibilidadeiss', 'issexigivel']);
        $outros = $this->findValueByAliases($inf, ['outrasinformacoes', 'observacoes', 'notes']);
        $valorCredito = $this->findValueByAliases($inf, ['valorcredito', 'creditvalue']);
        $baseCalculo = $this->findValueByAliases($valores, ['basecalculo', 'taxbase', 'calculationbase']);
        $valorLiquido = $this->findValueByAliases($valores, ['valorliquidonfse', 'valorservicos', 'totalliquid', 'liquidvalue', 'valornfse', 'total']);
        $valorServicos = $this->findValueByAliases($valores, ['valorservicos', 'servicevalue', 'total']);

        $prestadorRazao = $this->findValueByAliases($prestador, ['razaosocial', 'nomefantasia', 'name', 'companyname']);
        $prestadorFantasia = $this->findValueByAliases($prestador, ['nomefantasia', 'tradingname']);
        $prestadorEndereco = $this->findNodeByAliases($prestador, ['endereco', 'address']) ?? [];
        $prestadorContato = $this->findNodeByAliases($prestador, ['contato', 'contact']) ?? [];

        $tomadorRazao = $this->findValueByAliases($taker, ['razaosocial', 'nomefantasia', 'name', 'companyname']);
        $tomadorDoc = $this->findValueByAliases($taker, ['cpfcnpj', 'cnpj', 'cpf', 'document']);
        $tomadorEndereco = $this->findNodeByAliases($taker, ['endereco', 'address']) ?? [];
        $tomadorContato = $this->findNodeByAliases($taker, ['contato', 'contact']) ?? [];

        return [
            'Nfse' => [
                'InfNfse' => [
                    'Numero' => $numero,
                    'DataEmissao' => $dataEmissao,
                    'CodigoVerificacao' => $codigoVerificacao,
                    'DescricaoCodigoTributacaoMunicipio' => $descricaoServico ?? $codigoTributacao,
                    'OutrasInformacoes' => $outros,
                    'ValorCredito' => $valorCredito,
                    'PrestadorServico' => [
                        'RazaoSocial' => $prestadorRazao,
                        'NomeFantasia' => $prestadorFantasia ?? $prestadorRazao,
                        'Endereco' => is_array($prestadorEndereco) ? $prestadorEndereco : [],
                        'Contato' => is_array($prestadorContato) ? $prestadorContato : [],
                    ],
                    'ValoresNfse' => [
                        'BaseCalculo' => $baseCalculo,
                        'ValorLiquidoNfse' => $valorLiquido ?? $valorServicos,
                    ],
                    'DeclaracaoPrestacaoServico' => [
                        'InfDeclaracaoPrestacaoServico' => [
                            'Prestador' => [
                                'CpfCnpj' => array_filter([
                                    'Cnpj' => $this->findValueByAliases($prestador, ['cpfcnpj', 'cnpj', 'document']),
                                    'Cpf' => $this->findValueByAliases($prestador, ['cpf']),
                                ], static fn ($value) => filled($value)),
                            ],
                            'TomadorServico' => [
                                'RazaoSocial' => $tomadorRazao,
                                'IdentificacaoTomador' => [
                                    'CpfCnpj' => array_filter([
                                        'Cnpj' => $tomadorDoc,
                                        'Cpf' => $this->findValueByAliases($taker, ['cpf']),
                                    ], static fn ($value) => filled($value)),
                                ],
                                'Endereco' => is_array($tomadorEndereco) ? $tomadorEndereco : [],
                                'Contato' => is_array($tomadorContato) ? $tomadorContato : [],
                            ],
                            'Tomador' => [
                                'RazaoSocial' => $tomadorRazao,
                                'IdentificacaoTomador' => [
                                    'CpfCnpj' => array_filter([
                                        'Cnpj' => $tomadorDoc,
                                        'Cpf' => $this->findValueByAliases($taker, ['cpf']),
                                    ], static fn ($value) => filled($value)),
                                ],
                                'Endereco' => is_array($tomadorEndereco) ? $tomadorEndereco : [],
                                'Contato' => is_array($tomadorContato) ? $tomadorContato : [],
                            ],
                            'Servico' => [
                                'Discriminacao' => $descricaoServico,
                                'CodigoTributacaoMunicipio' => $codigoTributacao,
                                'ExigibilidadeISS' => $exigibilidadeIss,
                                'Valores' => is_array($valores) ? $valores : [],
                                'IssRetido' => $this->findValueByAliases($servico, ['issretido', 'istretido']),
                                'ItemListaServico' => $this->findValueByAliases($servico, ['itemlistaservico', 'itemservico']),
                                'CodigoCnae' => $this->findValueByAliases($servico, ['codigocnae', 'cnae']),
                                'MunicipioIncidencia' => $this->findValueByAliases($servico, ['municipioincidencia', 'municipio']),
                            ],
                            'OptanteSimplesNacional' => $this->findValueByAliases($inf, ['optantesimplesnacional']),
                            'IncentivoFiscal' => $this->findValueByAliases($inf, ['incentivofiscal']),
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function normalizeNota(mixed $nota): array
    {
        $xmlSource = data_get($nota, 'originalXml')
            ?: data_get($nota, 'xmlAbrasf')
            ?: data_get($nota, 'xmlAbrasfRtc')
            ?: data_get($nota, 'event.0.xml');

        $parsedXml = $this->normalizeXmlToArray($xmlSource)
            ?? $this->normalizeXmlToArray(data_get($nota, 'jsonDocument'))
            ?? [];

        $parsedXml = $this->buildCanonicalNfseDocument($parsedXml);

        $parsedXml['xml'] = $xmlSource ? base64_encode($xmlSource) : null;
        $parsedXml['__qive'] = [
            'id' => data_get($nota, 'id'),
            'origin' => data_get($nota, 'origin'),
            'status' => data_get($nota, 'status'),
            'flagErp' => data_get($nota, 'flagErp'),
            'event' => data_get($nota, 'event', []),
            'emitter' => data_get($nota, 'emitter', []),
            'taker' => data_get($nota, 'taker', []),
            'ownerRoles' => data_get($nota, 'ownerRoles', []),
            'documentIdentifier' => data_get($nota, 'documentIdentifier'),
            'queryIdentifier' => data_get($nota, 'queryIdentifier'),
            'originalXml' => data_get($nota, 'originalXml'),
            'xmlAbrasf' => data_get($nota, 'xmlAbrasf'),
            'xmlAbrasfRtc' => data_get($nota, 'xmlAbrasfRtc'),
            'jsonDocument' => data_get($nota, 'jsonDocument'),
        ];

        return $parsedXml;
    }

    public function localiza(Request $request, Companies $company): mixed
    {
        try {
            $payload = [
                'filter' => $this->buildFilter($request),
                'projection' => $this->buildProjection(),
            ];

            if ($request->filled('nfse_numero')) {
                $payload['queryIdentifier'] = trim((string) $request->get('nfse_numero'));
            } elseif ($request->filled('nfse_numero_inicial') || $request->filled('nfse_numero_final')) {
                $payload['queryIdentifier'] = implode('-', array_filter([
                    $request->get('nfse_numero_inicial'),
                    $request->get('nfse_numero_final'),
                ], static fn ($value) => filled($value)));
            } elseif ($request->filled('rps_numero')) {
                $payload['queryIdentifier'] = trim((string) $request->get('rps_numero'));
            }

            $paginator = $this->buildPaginator($request);
            if (! empty($paginator)) {
                $payload['paginator'] = $paginator;
            }

            $response = Http::withHeaders([
                'X-API-ID' => $company->api_id,
                'X-API-KEY' => $company->api_key,
                'X-Use-ApiGateway' => 'always',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post(self::ENDPOINT, $payload);

            $resp = $response->json();
            if (! is_array($resp)) {
                $resp = json_decode($response->body(), true) ?? [];
            }

            if (! $response->successful() && empty(data_get($resp, 'nfses'))) {
                throw new Exception(data_get($resp, 'status.message') ?? 'Nenhuma resposta válida foi retornada pela Qive.');
            }

            $nfses = data_get($resp, 'nfses', []);
            $notasArray = [];
            $accountingNotas = [];

            foreach ($nfses as $nota) {
                $normalized = $this->normalizeNota($nota);
                $notasArray[] = $normalized;

                $accountingNota = $this->normalizeXmlToArray(data_get($nota, 'jsonDocument'))
                    ?? $this->normalizeXmlToArray(data_get($nota, 'originalXml'))
                    ?? $this->normalizeXmlToArray(data_get($nota, 'xmlAbrasf'))
                    ?? $this->normalizeXmlToArray(data_get($nota, 'xmlAbrasfRtc'));

                if ($accountingNota) {
                    $accountingNotas[] = $accountingNota;
                }
            }

            if ($company->accounting == true || $company->accounting == 1 || $company->accounting == 'true') {
                $accounting = new AccountingService($company, 'Nfse', 'Onvio');
                $accounting->processInvoice($accountingNotas);
            }

            return [
                'success' => (int) data_get($resp, 'status.code', $response->status()) === 200,
                'code' => data_get($resp, 'status.code', $response->status()),
                'message' => data_get($resp, 'status.message') ?? $response->reason(),
                'endpoint' => self::ENDPOINT,
                'filter' => $payload['filter'],
                'notas' => base64_encode(json_encode($nfses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                'notasarray' => $notasArray,
                'page' => [
                    'nextPage' => data_get($resp, 'nextPage'),
                ],
                'count' => data_get($resp, 'total', count($nfses)),
                'signature' => data_get($resp, 'signature'),
            ];
        } catch (Exception $e) {
            Log::error('Erro no QiveService: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage() ?: 'Erro ao buscar NFS-e.',
            ];
        }
    }
}
