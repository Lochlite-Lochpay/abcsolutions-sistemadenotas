<?php

namespace App\Http\Controllers;

use App\Models\Companies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {            
        $user = Auth::user();   
        
        if(!$user->app_id){
            $user->update(['app_id' => Str::uuid()]);
        }
        
        function checkAccessAdmin($user){
            if($user->type == 'admin') {
                //check first access
                $marked = session()->get('markedadminfirstaccess', false); 
                if(!$marked) {
                    session()->put('markedadminfirstaccess', true);
                    return true;
                } else {
                    return false;
                }                           
            } else {
                return false;
            }
        }

        $companies = Companies::where('user_id', $user->id)
            ->when($request->query('search'), function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('cnpj', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard', [
            'search' => $request->query('search'),
            'companies' => $companies,  
            'adminfirstaccess' => checkAccessAdmin($user),
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();   
        
        return Inertia::render('Companies/Create');                                     
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cnpj' => 'required|string|max:14',
            'name' => 'required|string|max:255',
            'token' => 'required|string',   
            'token_qive' => 'nullable|string',   
            'api_id' => 'nullable|string',   
            'api_key' => 'nullable|string',  
            'client_id_accountings' => 'nullable|string',
            'client_key_accountings' => 'nullable|string',
            'client_audience_accountings' => 'nullable|string',
            'access_token_accountings' => 'nullable|string',
            'api_generate_integration_id_accountings' => 'nullable|string',
            'personal_integration_id_accountings' => 'nullable|string',
            'accounting' => 'nullable|boolean',
        ]);

        $user = Auth::user();   

        Companies::create([
            'user_id' => $user->id,
            'cnpj' => $request->cnpj,
            'name' => $request->name,
            'token' => $request->token, 
            'token_qive' => $request->token_qive,                   
            'api_id' => $request->api_id,
            'api_key' => $request->api_key,
            'client_id_accountings' => $request->client_id_accountings,
            'client_key_accountings' => $request->client_key_accountings,
            'client_audience_accountings' => $request->client_audience_accountings,
            'access_token_accountings' => $request->access_token_accountings,
            'api_generate_integration_id_accountings' => $request->api_generate_integration_id_accountings,
            'personal_integration_id_accountings' => $request->personal_integration_id_accountings,
            'accounting' => $request->has('accounting') ? true : false,
        ]);

        return redirect()->route('dashboard.home.index')->with('success', 'Empresa criada com sucesso!');                       
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
        $user = Auth::user();   

        $company = Companies::where('user_id', $user->id)->findOrFail($id);

        return Inertia::render('Companies/Edit', [
            'company' => $company,
        ]);                                 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'cnpj' => 'required|string|max:14',
            'name' => 'required|string|max:255',
            'token' => 'required|string', 
            'token_qive' => 'nullable|string', 
            'api_id' => 'nullable|string',
            'api_key' => 'nullable|string', 
            'client_id_accountings' => 'nullable|string',
            'client_key_accountings' => 'nullable|string',
            'client_audience_accountings' => 'nullable|string',
            'access_token_accountings' => 'nullable|string',
            'api_generate_integration_id_accountings' => 'nullable|string',
            'personal_integration_id_accountings' => 'nullable|string',                                 
        ]);

        $user = Auth::user();   

        $company = Companies::where('user_id', $user->id)->findOrFail($id);

        $company->update([
            'cnpj' => $request->cnpj,
            'name' => $request->name,
            'token' => $request->token,
            'token_qive' => $request->token_qive,       
            'api_id' => $request->api_id,                           
            'api_key' => $request->api_key,   
            'client_id_accountings' => $request->client_id_accountings,
            'client_key_accountings' => $request->client_key_accountings,
            'client_audience_accountings' => $request->client_audience_accountings,
            'access_token_accountings' => $request->access_token_accountings,
            'api_generate_integration_id_accountings' => $request->api_generate_integration_id_accountings,
            'personal_integration_id_accountings' => $request->personal_integration_id_accountings,
            'accounting' => $request->has('accounting') ? true : false,                                 
        ]);

        return redirect()->route('dashboard.home.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();   

        $company = Companies::where('user_id', $user->id)->findOrFail($id);

        $company->delete();

        return redirect()->route('dashboard.home.index')->with('success', 'Empresa excluída com sucesso!');
    }
}
