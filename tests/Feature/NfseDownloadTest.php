<?php

use App\Models\Companies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function nfseFakeCompany(User $user): Companies
{
    return Companies::create([
        'user_id' => $user->id,
        'cnpj' => '12.345.678/0001-90',
        'name' => 'ABC Solutions',
        'api_id' => 'api-id-test',
        'api_key' => 'api-key-test',
        'accounting' => false,
    ]);
}

function nfseFakeNote(string $id, string $numero, string $xml): array
{
    return [
        'id' => $id,
        'originalXml' => base64_encode($xml),
        'jsonDocument' => [
            'Nfse' => [
                'InfNfse' => [
                    'Numero' => $numero,
                ],
            ],
        ],
    ];
}

it('downloads a single nfse as xml', function () {
    $user = User::factory()->create();
    $company = nfseFakeCompany($user);

    Http::fake([
        'https://api.arquivei.com.br/v1/dfe/nfse' => Http::response([
            'status' => [
                'code' => 200,
                'message' => 'OK',
            ],
            'nfses' => [
                nfseFakeNote('321', '123', '<?xml version="1.0" encoding="UTF-8"?><CompNfse><Nfse><InfNfse><Numero>123</Numero></InfNfse></Nfse></CompNfse>'),
            ],
        ], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard.nfse.download', [
            'id' => $company->id,
            'webserver' => 'prestadas',
        ]));

    $response->assertOk();
    $response->assertDownload('nfse_123.xml');
    expect($response->streamedContent())->toContain('<CompNfse>');
    expect($response->streamedContent())->toContain('<Numero>123</Numero>');
});

it('downloads multiple nfse notes as a zip file', function () {
    $user = User::factory()->create();
    $company = nfseFakeCompany($user);

    Http::fake([
        'https://api.arquivei.com.br/v1/dfe/nfse' => Http::response([
            'status' => [
                'code' => 200,
                'message' => 'OK',
            ],
            'nfses' => [
                nfseFakeNote('321', '123', '<?xml version="1.0" encoding="UTF-8"?><CompNfse><Nfse><InfNfse><Numero>123</Numero></InfNfse></Nfse></CompNfse>'),
                nfseFakeNote('654', '456', '<?xml version="1.0" encoding="UTF-8"?><CompNfse><Nfse><InfNfse><Numero>456</Numero></InfNfse></Nfse></CompNfse>'),
            ],
        ], 200),
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard.nfse.download', [
            'id' => $company->id,
            'webserver' => 'prestadas',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/zip');
    $response->assertDownload();

    $zipPath = $response->baseResponse->getFile()->getPathname();
    $zip = new \ZipArchive;
    expect($zip->open($zipPath))->toBeTrue();
    expect($zip->numFiles)->toBe(2);
    expect($zip->getNameIndex(0))->toBe('nfse_123.xml');
    expect($zip->getNameIndex(1))->toBe('nfse_456.xml');
    $zip->close();
});
