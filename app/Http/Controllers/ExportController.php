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

use App\Models\Bids;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $bidId = null)
    {
        $user = auth()->user();
        if ($bidId) {
            $bid = Bids::where('user_id', $user->id)->where('id', $bidId)->with('files')->first();
            if (! $bid) {
                return response()->json(['message' => 'Bid não encontrado.'], 404);
            }

            return response()->streamDownload(function () use ($bid) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['id', 'user_id', 'name', 'description', 'files', 'created_at', 'updated_at']);
                fputcsv($handle, [
                    $bid->id,
                    $bid->user_id,
                    $bid->name,
                    $bid->description ?? 'Sem descrição',
                    collect($bid->files)->pluck('path')->map(function ($path) {
                        return url('storage/'.$path);
                    })->implode(', '),
                    $bid->created_at,
                    $bid->updated_at,
                ]);
                fclose($handle);
            }, "bid_{$bidId}.csv", [
                'Content-Type' => 'text/csv',
            ]);
        } else {
            return inertia('Export', [
                'bids' => Bids::where('user_id', auth()->id())->when($request->query('search'), function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })->paginate(),
                'search' => $request->query('search'),
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
