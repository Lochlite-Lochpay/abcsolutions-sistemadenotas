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

namespace App\Http\Middleware;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $target = storage_path('app/public');
        $link = public_path('storage');

        if (! File::exists($link)) {
            File::link($target, $link);
        }

        $dataShere = [
            'settings' => Settings::firstOrCreate(),
        ];
        if (session('isLoggedAdmin')) {
            $dataShere['isLoggedAdmin'] = session('isLoggedAdmin');
        }
        if (Session()->has('message')) {
            $dataShere['flash']['message'] = session('message');
        }

        return array_merge(parent::share($request), $dataShere);
    }
}
