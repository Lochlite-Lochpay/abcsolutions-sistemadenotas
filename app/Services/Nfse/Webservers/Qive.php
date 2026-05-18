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
            'ownerRoles' => [$this->ownerRoles($request)],
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

        if (is_string($decoded) && str_contains($decoded, '<')) {
            return $decoded;
        }

        $unzipped = is_string($decoded) ? @gzdecode($decoded) : false;

        if (is_string($unzipped) && str_contains($unzipped, '<')) {
            return $unzipped;
        }

        return $xmlDocument;
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

    protected function mapEnderecoNode(mixed $node): array
    {
        if (! is_array($node)) {
            return [];
        }

        return array_filter([
            'Endereco' => $this->findValueByAliases($node, ['endereco', 'logradouro', 'street']),
            'Numero' => $this->findValueByAliases($node, ['numero', 'number']),
            'Complemento' => $this->findValueByAliases($node, ['complemento', 'complement']),
            'Bairro' => $this->findValueByAliases($node, ['bairro', 'district']),
            'CodigoMunicipio' => $this->findValueByAliases($node, ['codigomunicipio', 'municipiocodigo', 'citycode']),
            'Uf' => $this->findValueByAliases($node, ['uf', 'estado', 'state']),
            'Cep' => $this->findValueByAliases($node, ['cep', 'zipcode']),
            'CodigoPais' => $this->findValueByAliases($node, ['codigopais', 'paiscodigo']),
        ], static fn ($value) => filled($value));
    }

    protected function mapContatoNode(mixed $node): array
    {
        if (! is_array($node)) {
            return [];
        }

        return array_filter([
            'Telefone' => $this->findValueByAliases($node, ['telefone', 'phone']),
            'Email' => $this->findValueByAliases($node, ['email', 'e-mail', 'mail']),
        ], static fn ($value) => filled($value));
    }

    protected function mapValoresNode(mixed $node): array
    {
        if (! is_array($node)) {
            return [];
        }

        return array_filter([
            'BaseCalculo' => $this->findValueByAliases($node, ['basecalculo', 'taxbase', 'calculationbase']),
            'ValorDeducoes' => $this->findValueByAliases($node, ['valordeducoes', 'deductions', 'deductionvalue']),
            'ValorPis' => $this->findValueByAliases($node, ['valorpis', 'pis']),
            'ValorCofins' => $this->findValueByAliases($node, ['valorcofins', 'cofins']),
            'ValorInss' => $this->findValueByAliases($node, ['valorinss', 'inss']),
            'ValorIr' => $this->findValueByAliases($node, ['valorir', 'ir']),
            'ValorCsll' => $this->findValueByAliases($node, ['valorcsll', 'csll']),
            'OutrasRetencoes' => $this->findValueByAliases($node, ['outrasretencoes', 'otherwithholdings']),
            'ValorIss' => $this->findValueByAliases($node, ['valoriss', 'iss']),
            'Aliquota' => $this->findValueByAliases($node, ['aliquota', 'rate']),
            'ValorLiquidoNfse' => $this->findValueByAliases($node, ['valorliquidonfse', 'totalliquid', 'liquidvalue', 'valornfse', 'total']),
            'ValorServicos' => $this->findValueByAliases($node, ['valorservicos', 'servicevalue', 'total']),
        ], static fn ($value) => filled($value));
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

        // OrgaoGerador: read directly from the parsed XML node.
        // The real XMLs only carry CodigoMunicipio inside OrgaoGerador; Uf lives in PrestadorServico>Endereco.
        $orgaoGeradorNode = $this->findNodeByAliases($inf, ['orgaogerador']) ?? [];
        $orgaoGeradorCodigoMunicipio = $this->findValueByAliases($orgaoGeradorNode, ['codigomunicipio'])
            ?? $this->findValueByAliases($inf, ['codigomunicipio']);
        // Uf is not present in OrgaoGerador in practice; fall back to PrestadorServico>Endereco>Uf.
        $orgaoGeradorUf = $this->findValueByAliases($orgaoGeradorNode, ['uf'])
            ?? $this->findValueByAliases(
                $this->findNodeByAliases($this->findNodeByAliases($root, ['prestadorservico', 'prestador', 'emitente', 'emitter']) ?? [], ['endereco', 'address']) ?? [],
                ['uf', 'estado', 'state']
            );

        $prestador = $this->findNodeByAliases($root, ['prestadorservico', 'prestador', 'emitente', 'emitter']) ?? [];
        $taker = $this->findNodeByAliases($root, ['tomadorservico', 'tomador', 'taker', 'recebedor', 'receiver']) ?? [];
        $servico = $this->findNodeByAliases($inf, ['servico', 'service']) ?? [];

        // ValoresNfse lives at InfNfse level; Servico>Valores lives inside the Servico node.
        // Both need to be consulted: ValoresNfse typically carries ValorLiquidoNfse/BaseCalculo/Aliquota/ValorIss,
        // while Servico>Valores carries ValorServicos, ValorCofins, ValorPis, ValorCsll, ValorDeducoes, etc.
        // We keep them separate so each can be queried from its canonical source.
        $valoresNfse = $this->findNodeByAliases($inf, ['valoresnfse']) ?? [];
        $servicoValoresNode = $this->findNodeByAliases($servico, ['valores', 'valor']) ?? [];
        // Unified fallback: if a field is absent in one, try the other.
        $valores = array_merge(
            is_array($servicoValoresNode) ? $servicoValoresNode : [],
            is_array($valoresNfse) ? $valoresNfse : [],
        );

        $numero = $this->findValueByAliases($inf, ['numero', 'numeronfse', 'nfsenumero', 'nfse', 'id']);
        $dataEmissao = $this->findValueByAliases($inf, ['dataemissao', 'emissao', 'emissiondate', 'createdat']);
        $codigoVerificacao = $this->findValueByAliases($inf, ['codigoverificacao', 'verificationcode', 'codigoverif']);
        $competencia = $this->findValueByAliases($inf, ['competencia', 'periodo']);
        $regimeEspecialTributacao = $this->findValueByAliases($inf, ['regimeespecialtributacao', 'regime']);
        $descricaoServico = $this->findValueByAliases($servico, ['discriminacao', 'descricao', 'description']);
        $codigoTributacao = $this->findValueByAliases($servico, ['codigotributacaomunicipio', 'codigotributacao', 'tributationcode']);
        $exigibilidadeIss = $this->findValueByAliases($servico, ['exigibilidadeiss', 'issexigivel']);
        $outros = $this->findValueByAliases($inf, ['outrasinformacoes', 'observacoes', 'notes']);
        $valorCredito = $this->findValueByAliases($inf, ['valorcredito', 'creditvalue']);
        // BaseCalculo may appear in ValoresNfse or Servico>Valores depending on the municipality's layout.
        $baseCalculo = $this->findValueByAliases($valoresNfse, ['basecalculo', 'taxbase', 'calculationbase'])
            ?? $this->findValueByAliases($servicoValoresNode, ['basecalculo', 'taxbase', 'calculationbase']);
        // ValorLiquidoNfse is authoritative from ValoresNfse; fallback to merged $valores.
        $valorLiquido = $this->findValueByAliases($valoresNfse, ['valorliquidonfse', 'totalliquid', 'liquidvalue', 'valornfse'])
            ?? $this->findValueByAliases($valores, ['valorliquidonfse', 'totalliquid', 'liquidvalue', 'valornfse', 'valorservicos', 'total']);
        // ValorServicos is canonical in Servico>Valores; fallback to ValoresNfse.
        $valorServicos = $this->findValueByAliases($servicoValoresNode, ['valorservicos', 'servicevalue', 'total'])
            ?? $this->findValueByAliases($valoresNfse, ['valorservicos', 'servicevalue', 'total']);

        $prestadorRazao = $this->findValueByAliases($prestador, ['razaosocial', 'nomefantasia', 'name', 'companyname']);
        $prestadorFantasia = $this->findValueByAliases($prestador, ['nomefantasia', 'tradingname']);
        $prestadorInscricaoMunicipal = $this->findValueByAliases($prestador, ['inscricaomunicipal', 'im']);
        $prestadorEndereco = $this->mapEnderecoNode($this->findNodeByAliases($prestador, ['endereco', 'address']));
        $prestadorContato = $this->mapContatoNode($this->findNodeByAliases($prestador, ['contato', 'contact']));
        $prestadorCpfCnpj = array_filter([
            'Cnpj' => $this->findValueByAliases($prestador, ['cpfcnpj.cnpj', 'identificacaoprestador.cpfcnpj.cnpj', 'cnpj', 'document']),
            'Cpf' => $this->findValueByAliases($prestador, ['cpfcnpj.cpf', 'identificacaoprestador.cpfcnpj.cpf', 'cpf']),
        ], static fn ($value) => filled($value));

        $rps = [
            'IdentificacaoRps' => array_filter([
                'Numero' => $this->findValueByAliases($inf, ['rps.identificacaorps.numero', 'identificacaorps.numero', 'rpsnumero']),
                'Serie' => $this->findValueByAliases($inf, ['rps.identificacaorps.serie', 'identificacaorps.serie', 'rpsserie']),
                'Tipo' => $this->findValueByAliases($inf, ['rps.identificacaorps.tipo', 'identificacaorps.tipo', 'rpstipo']),
            ], static fn ($value) => filled($value)),
            'DataEmissao' => $this->findValueByAliases($inf, ['rps.dataemissao', 'dataemissao']),
            'Status' => $this->findValueByAliases($inf, ['rps.status', 'status']),
        ];

        $tomadorRazao = $this->findValueByAliases($taker, ['razaosocial', 'nomefantasia', 'name', 'companyname']);
        $tomadorDoc = $this->findValueByAliases($taker, ['cpfcnpj', 'cnpj', 'cpf', 'document']);
        $tomadorInscricaoMunicipal = $this->findValueByAliases($taker, ['inscricaomunicipal', 'im']);
        $tomadorEndereco = $this->mapEnderecoNode($this->findNodeByAliases($taker, ['endereco', 'address']));
        $tomadorContato = $this->mapContatoNode($this->findNodeByAliases($taker, ['contato', 'contact']));
        // servicoValores must come from Servico>Valores (ValorServicos, ValorCofins etc.) merged with
        // ValoresNfse (ValorLiquidoNfse, BaseCalculo, Aliquota) so all fields are populated.
        $servicoValores = $this->mapValoresNode(array_merge(
            is_array($valoresNfse) ? $valoresNfse : [],
            is_array($servicoValoresNode) ? $servicoValoresNode : [],
        ));

        return [
            'Nfse' => [
                'InfNfse' => [
                    'Numero' => $numero,
                    'DataEmissao' => $dataEmissao,
                    'CodigoVerificacao' => $codigoVerificacao,
                    'Competencia' => $competencia,
                    'Rps' => $rps,
                    'RegimeEspecialTributacao' => $regimeEspecialTributacao,
                    'DescricaoCodigoTributacaoMunicipio' => $descricaoServico ?? $codigoTributacao,
                    'OutrasInformacoes' => $outros,
                    'ValorCredito' => $valorCredito,
                    'PrestadorServico' => [
                        'RazaoSocial' => $prestadorRazao,
                        'NomeFantasia' => $prestadorFantasia ?? $prestadorRazao,
                        'IdentificacaoPrestador' => [
                            'CpfCnpj' => $prestadorCpfCnpj,
                            'InscricaoMunicipal' => $prestadorInscricaoMunicipal,
                        ],
                        'Endereco' => is_array($prestadorEndereco) ? $prestadorEndereco : [],
                        'Contato' => is_array($prestadorContato) ? $prestadorContato : [],
                    ],
                    'ValoresNfse' => [
                        'BaseCalculo' => $baseCalculo,
                        'ValorLiquidoNfse' => $valorLiquido ?? $valorServicos,
                        'ValorServicos' => $valorServicos,
                    ],
                    'OrgaoGerador' => [
                        'CodigoMunicipio' => $orgaoGeradorCodigoMunicipio ?? '',
                        'Uf' => $orgaoGeradorUf ?? '',
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
                                    'InscricaoMunicipal' => $tomadorInscricaoMunicipal,
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
                                    'InscricaoMunicipal' => $tomadorInscricaoMunicipal,
                                ],
                                'Endereco' => is_array($tomadorEndereco) ? $tomadorEndereco : [],
                                'Contato' => is_array($tomadorContato) ? $tomadorContato : [],
                            ],
                            'Servico' => [
                                'Discriminacao' => $descricaoServico,
                                'CodigoTributacaoMunicipio' => $codigoTributacao,
                                'ExigibilidadeISS' => $exigibilidadeIss,
                                'Valores' => $servicoValores,
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
            ?: data_get($nota, 'xml')
            ?: data_get($nota, 'event.0.xml');
        $xmlString = $this->extractXmlString($xmlSource);

        $parsedXml = $this->normalizeXmlToArray($xmlString)
            ?? $this->normalizeXmlToArray(data_get($nota, 'jsonDocument'))
            ?? [];

        $parsedXml = $this->buildCanonicalNfseDocument($parsedXml);

        $parsedXml['xml'] = $xmlString ? base64_encode($xmlString) : null;
        $parsedXml['xmlBase64'] = $parsedXml['xml'];
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
            'xml' => data_get($nota, 'xml'),
            'jsonDocument' => data_get($nota, 'jsonDocument'),
        ];

        return $parsedXml;
    }

    protected function roleEndpoint(Request $request): string
    {
        $role = $this->requestedRole($request) === 'taker' ? 'received' : 'emitted';

        return "https://api.arquivei.com.br/v1/nfse/{$role}";
    }

    protected function buildRoleQuery(Request $request, Companies $company): array
    {
        $query = array_filter([
            'cnpj' => $this->normalizeDigits((string) ($company->cnpj ?? '')),
            'id' => $request->get('nfse_numero'),
            'limit' => 50,
        ], static fn ($value) => filled($value));

        if ($request->filled('data_emissao_inicial')) {
            $query['created_at[from]'] = $request->get('data_emissao_inicial');
        }

        if ($request->filled('data_emissao_final')) {
            $query['created_at[to]'] = $request->get('data_emissao_final');
        }

        return $query;
    }

    protected function fetchByRoleEndpoint(Request $request, Companies $company): array
    {
        $endpoint = $this->roleEndpoint($request);
        $response = Http::acceptJson()
            ->timeout(20)
            ->withHeaders([
                'X-API-ID' => $company->api_id,
                'X-API-KEY' => $company->api_key,
                'X-Use-ApiGateway' => 'always',
            ])
            ->get($endpoint, $this->buildRoleQuery($request, $company));

        $resp = $response->json();
        if (! is_array($resp)) {
            $resp = json_decode($response->body(), true) ?? [];
        }

        if (! $response->successful()) {
            throw new Exception(data_get($resp, 'status.message') ?? data_get($resp, 'error.message') ?? "Qive retornou HTTP {$response->status()}.");
        }

        $nfses = data_get($resp, 'data', data_get($resp, 'nfses', []));

        return [
            'endpoint' => $endpoint,
            'response' => $resp,
            'nfses' => is_array($nfses) ? $nfses : [],
        ];
    }

    public function localiza(Request $request, Companies $company, bool $processAccounting = true): mixed
    {
        try {
            $filter = $this->buildFilter($request);
            if (! $request->filled('document')) {
                $companyDocument = $this->normalizeDigits((string) ($company->cnpj ?? ''));
                if ($companyDocument) {
                    $ownerKey = $this->requestedRole($request) === 'taker' ? 'takerCnpj' : 'emitterCnpj';
                    $filter[$ownerKey] = array_values(array_unique(array_filter([
                        ...($filter[$ownerKey] ?? []),
                        $companyDocument,
                    ])));
                    $filter['owners'] = array_values(array_unique(array_filter([
                        ...($filter['owners'] ?? []),
                        $companyDocument,
                    ])));
                }
            }

            $payload = [
                'filters' => $filter,
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

            $endpoint = self::ENDPOINT;
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

            $statusCode = $response->status();
            $nfses = data_get($resp, 'nfses', []);

            if (! $response->successful() || empty($nfses)) {
                $fallback = $this->fetchByRoleEndpoint($request, $company);
                $endpoint = $fallback['endpoint'];
                $resp = $fallback['response'];
                $nfses = $fallback['nfses'];
                $statusCode = 200;
            }

            $notasArray = [];
            $accountingNotas = [];

            foreach ($nfses as $nota) {
                $normalized = $this->normalizeNota($nota);
                $notasArray[] = $normalized;

                if (data_get($normalized, 'Nfse.InfNfse.Numero')) {
                    $accountingNotas[] = $normalized;
                }
            }

            if ($processAccounting && ($company->accounting == true || $company->accounting == 1 || $company->accounting == 'true')) {
                try {
                    $accounting = new AccountingService($company, 'Nfse', 'Onvio');
                    $accounting->processInvoice($accountingNotas);
                } catch (Exception $e) {
                    Log::error('Erro ao enviar NFS-e para contabilidade: '.$e->getMessage());
                }
            }

            return [
                'success' => (int) data_get($resp, 'status.code', $statusCode) === 200,
                'code' => data_get($resp, 'status.code', $statusCode),
                'message' => data_get($resp, 'status.message') ?? $response->reason(),
                'endpoint' => $endpoint,
                'filter' => $payload['filters'],
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
