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

use Illuminate\Http\Request;
use App\Models\Invoices;
use App\Models\Companies;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\Accounting\AccountingService;

class AccountingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($request->query('home')) {
            $company = Companies::where('user_id', $user->id)->findOrFail($request->query('home'));
            $notas = Invoices::orderBy('created_at', 'desc')
                ->get();
            return Inertia::render('Accountings/Index', [
                'company' => $company,
                'notas' => $notas,
            ]);
        } else {
            return Inertia::render('Accountings/SelectCompany', [
                'companies' => Companies::where('user_id', $user->id)->paginate()
            ]);
        }
    }

    public function checkSend(Companies $company, $invoiceNumber, $id)
    {
        $invoice = Invoices::where('id', $invoiceNumber)->first();
        $accounting = new AccountingService($company, 'Nfse', 'Onvio');
        $data = $accounting->checkSend($id);
        $invoice->accountings_response = $data;
        $invoice->save();
        return redirect()->back();
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
