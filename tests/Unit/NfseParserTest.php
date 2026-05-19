<?php

use App\Models\Companies;
use App\Services\Nfse\Webservers\Qive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

it('normalizes qive xml fields that the legacy page shows', function (): void {
    Http::fake([
        'https://api.arquivei.com.br/v1/dfe/nfse' => Http::response([
            'status' => [
                'code' => 200,
                'message' => 'OK',
            ],
            'nfses' => [
                [
                    'id' => 'NF-1',
                    'originalXml' => base64_encode('<CompNfse xmlns="http://www.abrasf.org.br/nfse.xsd"><Nfse><InfNfse Id="NF-1"><Numero>15</Numero><CodigoVerificacao>ABC123</CodigoVerificacao><DataEmissao>2026-05-01T14:46:54</DataEmissao><Competencia>2026-04-01</Competencia><Rps><IdentificacaoRps><Numero>7289854</Numero><Serie>02026</Serie></IdentificacaoRps><DataEmissao>2026-04-30</DataEmissao><Status>1</Status></Rps><ValoresNfse><BaseCalculo>120.00</BaseCalculo><ValorLiquidoNfse>1000.00</ValorLiquidoNfse></ValoresNfse><PrestadorServico><IdentificacaoPrestador><CpfCnpj><Cnpj>62699373000150</Cnpj></CpfCnpj><InscricaoMunicipal>103995700186</InscricaoMunicipal></IdentificacaoPrestador><RazaoSocial>C C SERVICOS ODONTOLOGICOS LTDA</RazaoSocial><Endereco><Endereco>Tancredo Neves</Endereco><Numero>000909</Numero><Bairro>CAMINHO DAS ARVORES</Bairro><CodigoMunicipio>2927408</CodigoMunicipio><Uf>BA</Uf><Cep>41820020</Cep></Endereco></PrestadorServico><DeclaracaoPrestacaoServico><InfDeclaracaoPrestacaoServico><Competencia>2026-04-01</Competencia><Servico><Valores><ValorServicos>1000.00</ValorServicos><ValorPis>0.00</ValorPis><ValorCofins>0.00</ValorCofins><ValorCsll>0.00</ValorCsll><Discriminacao>REFERENTE SERVICOS ODONTOLOGICOS</Discriminacao><CodigoTributacaoMunicipio>412</CodigoTributacaoMunicipio><ItemListaServico>041201</ItemListaServico><ExigibilidadeISS>1</ExigibilidadeISS></Valores><IssRetido>2</IssRetido><MunicipioIncidencia>2927408</MunicipioIncidencia></Servico><TomadorServico><IdentificacaoTomador><CpfCnpj><Cpf>21577196520</Cpf></CpfCnpj></IdentificacaoTomador><RazaoSocial>TERMUTE MANOLITA GUIMARAES NILO DANTAS</RazaoSocial><Endereco><Endereco>Rua B</Endereco><Numero>456</Numero><Bairro>Centro</Bairro><CodigoMunicipio>2927408</CodigoMunicipio><Uf>BA</Uf><Cep>40111000</Cep></Endereco></TomadorServico><OptanteSimplesNacional>1</OptanteSimplesNacional><IncentivoFiscal>2</IncentivoFiscal></InfDeclaracaoPrestacaoServico></DeclaracaoPrestacaoServico></InfNfse></Nfse></CompNfse>')),
                ],
            ],
        ], 200),
    ]);

    $company = new Companies([
        'id' => 1,
        'user_id' => 1,
        'cnpj' => '62699373000150',
        'api_id' => 'api-id',
        'api_key' => 'api-key',
    ]);

    $request = Request::create('/dashboard/nfse', 'GET', [
        'id' => $company->id,
        'webserver' => 'prestadas',
    ]);

    $result = (new Qive)->localiza($request, $company, false);
    $nota = $result['notasarray'][0];

    expect($nota['Nfse']['InfNfse']['Numero'])->toBe('15');
    expect($nota['Nfse']['InfNfse']['CodigoVerificacao'])->toBe('ABC123');
    expect($nota['Nfse']['InfNfse']['Competencia'])->toBe('2026-04-01');
    expect($nota['Nfse']['InfNfse']['Rps']['IdentificacaoRps']['Numero'])->toBe('7289854');
    expect($nota['Nfse']['InfNfse']['Rps']['IdentificacaoRps']['Serie'])->toBe('02026');
    expect($nota['Nfse']['InfNfse']['PrestadorServico']['IdentificacaoPrestador']['InscricaoMunicipal'])->toBe('103995700186');
    expect($nota['Nfse']['InfNfse']['PrestadorServico']['Endereco']['Endereco'])->toBe('Tancredo Neves');
    expect($nota['Nfse']['InfNfse']['PrestadorServico']['Endereco']['Cep'])->toBe('41820020');
    expect($nota['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['RazaoSocial'])->toBe('TERMUTE MANOLITA GUIMARAES NILO DANTAS');
    expect($nota['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['TomadorServico']['Endereco']['Endereco'])->toBe('Rua B');
    expect($nota['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Servico']['Valores']['ValorServicos'])->toBe('1000.00');
    expect($nota['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Servico']['Valores']['ValorPis'])->toBe('0.00');
    expect($nota['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Servico']['Valores']['ValorCofins'])->toBe('0.00');
    expect($nota['Nfse']['InfNfse']['DeclaracaoPrestacaoServico']['InfDeclaracaoPrestacaoServico']['Servico']['Valores']['ValorCsll'])->toBe('0.00');
});
