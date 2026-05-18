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

namespace App\Http\Controllers;

use App\Models\Companies;
use App\Services\Nfse\NfseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use ZipArchive;

class NfseController extends Controller
{
    private function resolveCompanyIdFromRequest(Request $request): ?string
    {
        $direct = $request->query('id') ?? $request->query('home') ?? $request->query('?home');
        if (filled($direct)) {
            return (string) $direct;
        }

        foreach ($request->query() as $key => $value) {
            $normalizedKey = ltrim(urldecode((string) $key), '?');
            if (in_array($normalizedKey, ['id', 'home'], true) && filled($value)) {
                return (string) $value;
            }
        }

        $uriQuery = parse_url($request->getRequestUri(), PHP_URL_QUERY) ?: '';
        parse_str((string) $uriQuery, $rawQuery);
        foreach ($rawQuery as $key => $value) {
            $normalizedKey = ltrim(urldecode((string) $key), '?');
            if (in_array($normalizedKey, ['id', 'home'], true) && filled($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->resolveCompanyIdFromRequest($request);
        if ($companyId) {
            $company = Companies::where('user_id', $user->id)->findOrFail($companyId);

            $nfseService = new NfseService($company, 'Qive');
            $nfseResult = $nfseService->localiza($request);

            return Inertia::render('Nfse', array_merge([
                'company' => $company,
            ], $nfseResult, $request->all()));
        } else {
            return Inertia::render('Companies/SelectCompany', [
                'companies' => Companies::where('user_id', $user->id)->paginate(),
            ]);
        }
    }

    private function decodeXmlContent(?string $xml): ?string
    {
        if (! filled($xml)) {
            return null;
        }

        $xml = preg_replace('/^data:[^,]+,/', '', $xml);
        $decoded = base64_decode((string) $xml, true);

        if ($decoded !== false && filled($decoded)) {
            return $decoded;
        }

        return $xml;
    }

    private function sanitizeDownloadName(string $name): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);

        return trim((string) $sanitized, '_') ?: 'nfse';
    }

    private function extractDownloadFiles(array $notasarray): array
    {
        $files = [];

        foreach ($notasarray as $index => $nota) {
            $xml = data_get($nota, 'xmlBase64') ?: data_get($nota, 'xml');
            $xmlContent = $this->decodeXmlContent(is_string($xml) ? $xml : null);

            if (! filled($xmlContent)) {
                continue;
            }

            $numero = data_get($nota, 'Nfse.InfNfse.Numero')
                ?: data_get($nota, 'numero')
                ?: data_get($nota, '__qive.id')
                ?: data_get($nota, '__qive.queryIdentifier')
                ?: (string) ($index + 1);

            $files[] = [
                'name' => $this->sanitizeDownloadName('nfse_'.$numero).'.xml',
                'content' => $xmlContent,
            ];
        }

        return $files;
    }

    private function createZipFile(array $files): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'nfse_zip_');
        if ($zipPath === false) {
            throw new \RuntimeException('Não foi possível criar o arquivo temporário do ZIP.');
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Não foi possível gerar o ZIP das notas.');
        }

        foreach ($files as $file) {
            $zip->addFromString($file['name'], $file['content']);
        }

        $zip->close();

        return $zipPath;
    }

    public function download(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->resolveCompanyIdFromRequest($request);

        abort_unless($companyId, 404);

        $company = Companies::where('user_id', $user->id)->findOrFail($companyId);
        $nfseService = new NfseService($company, 'Qive');
        $nfseResult = $nfseService->localiza($request, false);
        $notasarray = is_array(data_get($nfseResult, 'notasarray')) ? data_get($nfseResult, 'notasarray') : [];
        $files = $this->extractDownloadFiles($notasarray);

        abort_unless(count($files) > 0, 404, 'Nenhuma XML foi encontrada para download.');

        if (count($files) === 1) {
            $file = $files[0];

            return response()->streamDownload(function () use ($file): void {
                echo $file['content'];
            }, $file['name'], [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ]);
        }

        $zipPath = $this->createZipFile($files);
        $downloadName = $this->sanitizeDownloadName('nfse_'.$company->id.'_'.now()->format('Ymd_His')).'.zip';

        return response()->download($zipPath, $downloadName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
