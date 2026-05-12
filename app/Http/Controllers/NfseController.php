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
use CloudDfe\SdkPHP\Base;
use CloudDfe\SdkPHP\Nfe;
use CloudDfe\SdkPHP\Nfse;

class NfseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($request->query('home')) {
            $company = Companies::where('user_id', $user->id)->findOrFail($request->query('home'));
    
            $nfseService = new NfseService($company, 'Qive');
            $nfseResult = $nfseService->localiza($request);
            //dd($nfseResult);
            return Inertia::render('Nfse', array_merge([
                'company' => $company,
            ], $nfseResult, $request->all()));
        } else {
            return Inertia::render('Companies/SelectCompany', [
                'companies' => Companies::where('user_id', $user->id)->paginate()
            ]);
        }
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
