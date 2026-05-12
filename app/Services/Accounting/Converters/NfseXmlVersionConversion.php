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

namespace App\Services\Accounting\Converters;

use Illuminate\Support\Facades\Log;

class NfseXmlVersionConversion
{
    public function convert(array $notasArray): mixed
    {
        try {// dd($notasArray);
            $novaXml = new \SimpleXMLElement('<CompNfse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.abrasf.org.br/nfse.xsd" />');
            if (true) {
                $nfse = $novaXml->addChild('Nfse');
                $nfse->addAttribute('versao', '1.00');

                $infNfse = $nfse->addChild('InfNfse');
                $infNfse->addChild('Numero', $notasArray['Nfse']['InfNfse']['Numero'] ?? '');
                $infNfse->addChild('CodigoVerificacao', $notasArray['Nfse']['InfNfse']['CodigoVerificacao'] ?? '');
                $infNfse->addChild('DataEmissao', $notasArray['Nfse']['InfNfse']['DataEmissao'] ?? '');

                // ValoresNfse
                $valoresNfse = $infNfse->addChild('ValoresNfse');
                $valoresNfse->addChild('BaseCalculo', $notasArray['Nfse']['InfNfse']['ValoresNfse']['BaseCalculo'] ?? '');
                $valoresNfse->addChild('Aliquota', $notasArray['Nfse']['InfNfse']['ValoresNfse']['Aliquota'] ?? '');
                $valoresNfse->addChild('ValorIss', $notasArray['Nfse']['InfNfse']['ValoresNfse']['ValorIss'] ?? '');
                $valoresNfse->addChild('ValorLiquidoNfse', $notasArray['Nfse']['InfNfse']['ValoresNfse']['ValorLiquidoNfse'] ?? '');

                // PrestadorServico
                $prestador = $infNfse->addChild('PrestadorServico');
                $identPrestador = $prestador->addChild('IdentificacaoPrestador');
                $cpfCnpj = $identPrestador->addChild('CpfCnpj');
                $cpfCnpj->addChild('Cnpj', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Prestador']['CpfCnpj']['Cnpj'] ?? '');
                $identPrestador->addChild('InscricaoMunicipal', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Prestador']['InscricaoMunicipal'] ?? '');
                $prestador->addChild('RazaoSocial', $notasArray['Nfse']['InfNfse']['PrestadorServico']['RazaoSocial'] ?? '');
                $prestador->addChild('NomeFantasia', $notasArray['Nfse']['InfNfse']['PrestadorServico']['NomeFantasia'] ?? '');

                $endereco = $prestador->addChild('Endereco');
                $endereco->addChild('Endereco', $notasArray['Nfse']['InfNfse']['PrestadorServico']['Endereco']['Endereco'] ?? '');
                $endereco->addChild('Numero', $notasArray['Nfse']['InfNfse']['PrestadorServico']['Endereco']['Numero'] ?? '');
                $endereco->addChild('Bairro', $notasArray['Nfse']['InfNfse']['PrestadorServico']['Endereco']['Bairro'] ?? '');
                $endereco->addChild('CodigoMunicipio', $notasArray['Nfse']['InfNfse']['PrestadorServico']['Endereco']['CodigoMunicipio'] ?? '');
                $endereco->addChild('CodigoPais', @$notasArray['Nfse']['InfNfse']['PrestadorServico']['Endereco']['CodigoPais'] ?? 'BR');
                $endereco->addChild('Cep', $notasArray['Nfse']['InfNfse']['PrestadorServico']['Endereco']['Cep'] ?? '');

                $contato = $prestador->addChild('Contato');
                $contato->addChild('Telefone', $notasArray['Nfse']['InfNfse']['PrestadorServico']['Contato']['Telefone'] ?? '');

                // OrgaoGerador
                $orgaoGerador = $infNfse->addChild('OrgaoGerador');
                $orgaoGerador->addChild('CodigoMunicipio', $notasArray['Nfse']['InfNfse']['OrgaoGerador']['CodigoMunicipio']);
                $orgaoGerador->addChild('Uf', $notasArray['Nfse']['InfNfse']['OrgaoGerador']['Uf']);

                // DeclaracaoPrestacaoServico
                $dps = $infNfse->addChild('DeclaracaoPrestacaoServico');
                $infDps = $dps->addChild('InfDeclaracaoPrestacaoServico');
                $infDps->addChild('Competencia', $notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Competencia']);

                // Servicos (pode ser array ou único)
                $servicos = $notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Servico'];
                if (isset($servicos[0])) {
                    foreach ($servicos as $servicoArr) {
                        $servico = $infDps->addChild('Servico');
                        $valores = $servico->addChild('Valores');
                        $valores->addChild('ValorServicos', $servicoArr['Valores']['ValorServicos'] ?? '');
                        $valores->addChild('ValorDeducoes', $servicoArr['Valores']['ValorDeducoes'] ?? '');
                        $valores->addChild('ValorPis', $servicoArr['Valores']['ValorPis'] ?? '');
                        $valores->addChild('ValorCofins', $servicoArr['Valores']['ValorCofins'] ?? '');
                        $valores->addChild('ValorInss', $servicoArr['Valores']['ValorInss'] ?? '');
                        $valores->addChild('ValorIr', $servicoArr['Valores']['ValorIr'] ?? '');
                        $valores->addChild('ValorCsll', $servicoArr['Valores']['ValorCsll'] ?? '');
                        $valores->addChild('OutrasRetencoes', $servicoArr['Valores']['OutrasRetencoes'] ?? '');
                        $valores->addChild('ValorIss', $servicoArr['Valores']['ValorIss'] ?? '');
                        $valores->addChild('Aliquota', $servicoArr['Valores']['Aliquota'] ?? '');
                        $servico->addChild('IssRetido', $servicoArr['IssRetido'] ?? '');
                        $servico->addChild('ItemListaServico', $servicoArr['ItemListaServico'] ?? '');
                        $servico->addChild('Discriminacao', $servicoArr['Discriminacao'] ?? '');
                        $servico->addChild('CodigoMunicipio', $servicoArr['CodigoMunicipio'] ?? '');
                        $servico->addChild('CodigoPais', @$servicoArr['CodigoPais'] ?? 'BR');
                        $servico->addChild('ExigibilidadeISS', $servicoArr['ExigibilidadeISS'] ?? '');
                        $servico->addChild('MunicipioIncidencia', $servicoArr['MunicipioIncidencia'] ?? '');
                    }
                } else {
                    $servicoArr = $servicos;
                    $servico = $infDps->addChild('Servico');
                    $valores = $servico->addChild('Valores');
                    $valores->addChild('ValorServicos', $servicoArr['Valores']['ValorServicos']);
                    $valores->addChild('ValorDeducoes', $servicoArr['Valores']['ValorDeducoes']);
                    $valores->addChild('ValorPis', $servicoArr['Valores']['ValorPis']);
                    $valores->addChild('ValorCofins', $servicoArr['Valores']['ValorCofins']);
                    $valores->addChild('ValorInss', $servicoArr['Valores']['ValorInss']);
                    $valores->addChild('ValorIr', $servicoArr['Valores']['ValorIr']);
                    $valores->addChild('ValorCsll', $servicoArr['Valores']['ValorCsll']);
                    $valores->addChild('OutrasRetencoes', $servicoArr['Valores']['OutrasRetencoes']);
                    $valores->addChild('ValorIss', $servicoArr['Valores']['ValorIss']);
                    $valores->addChild('Aliquota', $servicoArr['Valores']['Aliquota']);
                    $servico->addChild('IssRetido', $servicoArr['IssRetido']);
                    $servico->addChild('ItemListaServico', $servicoArr['ItemListaServico']);
                    $servico->addChild('Discriminacao', $servicoArr['Discriminacao']);
                    $servico->addChild('CodigoMunicipio', $servicoArr['CodigoMunicipio']);
                    $servico->addChild('CodigoPais', @$servicoArr['CodigoPais'] ?? 'BR');
                    $servico->addChild('ExigibilidadeISS', $servicoArr['ExigibilidadeISS']);
                    $servico->addChild('MunicipioIncidencia', $servicoArr['MunicipioIncidencia']);
                }

                // Prestador
                $prestadorDps = $infDps->addChild('Prestador');
                $cpfCnpjPrestador = $prestadorDps->addChild('CpfCnpj');
                $cpfCnpjPrestador->addChild('Cnpj', $notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Prestador']['CpfCnpj']['Cnpj']);
                $prestadorDps->addChild('InscricaoMunicipal', $notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Prestador']['InscricaoMunicipal']);

                // Tomador
                $tomador = $infDps->addChild('Tomador');
                $identTomador = $tomador->addChild('IdentificacaoTomador');
                $cpfCnpjTomador = $identTomador->addChild('CpfCnpj');
                $cpfCnpjTomador->addChild('Cnpj', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['IdentificacaoTomador']['CpfCnpj']['Cnpj'] ?? @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['IdentificacaoTomador']['CpfCnpj']['Cpf']);
                $identTomador->addChild('InscricaoMunicipal', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['IdentificacaoTomador']['InscricaoMunicipal']);
                $tomador->addChild('RazaoSocial', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['RazaoSocial']);

                $enderecoTomador = $tomador->addChild('Endereco');
                $enderecoTomador->addChild('Endereco', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['Endereco']['Endereco']);
                $enderecoTomador->addChild('Numero', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['Endereco']['Numero']);
                $enderecoTomador->addChild('Bairro', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['Endereco']['Bairro']);
                $enderecoTomador->addChild('CodigoMunicipio', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['Endereco']['CodigoMunicipio']);
                $enderecoTomador->addChild('Cep', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['Endereco']['Cep']);

                $contatoTomador = $tomador->addChild('Contato');
                $contatoTomador->addChild('Telefone', @$notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['Contato']['Telefone']);

                $infDps->addChild('OptanteSimplesNacional', $notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['OptanteSimplesNacional']);
                $infDps->addChild('IncentivoFiscal', $notasArray['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['IncentivoFiscal']);

                return [
                    'success' => true,
                    'xml' => $novaXml->asXML(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao gerar XML.',
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao converter XML: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage() ?? 'Erro ao gerar XML.',
            ];
        }
    }
}
