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
use App\Http\Controllers\AdminController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/auth/redirect', function () {
    $settings = \App\Models\Settings::first();
    if ($settings->auth_social_media == 0) {
        return redirect()->route('login');
    } else {
        Config::set('services.google.client_id', $settings->auth_client_id ?? env('GOOGLE_CLIENT_ID'));
        Config::set('services.google.client_secret', $settings->auth_client_secret ?? env('GOOGLE_CLIENT_SECRET'));

        return Socialite::driver('google')->redirect();
    }
})->name('auth.redirect');

Route::get('/auth/callback', function () {
    $provider = Socialite::driver('google')->user();

    $user = User::updateOrCreate([
        'google_id' => $provider->id,
    ], [
        'name' => $provider->name,
        'email' => $provider->email,
        'google_token' => $provider->token,
        'google_refresh_token' => $provider->refreshToken,
    ]);

    Auth::login($user);

    return redirect('/dashboard');
})->name('auth.callback');

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('welcome');

Route::get('/dashboard', function () {
    return redirect()->route('dashboard.home.index');
})->name('dashboard');

Route::prefix('dashboard')->name('dashboard.')->middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('nfse/download', [App\Http\Controllers\NfseController::class, 'download'])->name('nfse.download');
    Route::resource('nfse', App\Http\Controllers\NfseController::class)->names('nfse');
    Route::resource('accounting', App\Http\Controllers\AccountingController::class)->names('accounting');
    Route::get('accounting/checksend/{company}/{invoice}/{id}', [App\Http\Controllers\AccountingController::class, 'checkSend'])->name('accounting.checkSend');
    Route::resource('/home', App\Http\Controllers\HomeController::class)->names('home');
    Route::middleware(\App\Http\Middleware\IsAdmin::class)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/home', [AdminController::class, 'index'])->name('home');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/personalize', [AdminController::class, 'personalize'])->name('personalize');
        Route::put('/personalize', [AdminController::class, 'updatePersonalize'])->name('settings.update');
        Route::get('export-user-bid/{id?}', [AdminController::class, 'exportUserBids'])->name('export');
        Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    });
});
