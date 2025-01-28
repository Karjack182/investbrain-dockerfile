<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HoldingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\TransactionController;
use App\Interfaces\MarketData\MarketDataInterface;
use App\Http\Controllers\ConnectedAccountController;
use App\Http\Controllers\InvitedOnboardingController;
use Laravel\Jetstream\Http\Controllers\Livewire\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Livewire\TermsOfServiceController;

Route::get('/test', function() {
    dd(app(MarketDataInterface::class)->quote('BTCUSD'));
});
Route::get('/', function () {
    if (!config('investbrain.self_hosted', true) && View::exists('landing-page::index')) {
        
        return view('landing-page::index');
    }

    return redirect(route('dashboard'));
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::view('/import-export', 'import-export')->name('import-export');
    // ->middleware('verified');

    Route::get('/portfolio/create', [PortfolioController::class, 'create'])->name('portfolio.create');
    Route::get('/portfolio/{portfolio}', [PortfolioController::class, 'show'])->name('portfolio.show');

    Route::get('/portfolio/{portfolio}/{symbol}', [HoldingController::class, 'show'])->name('holding.show');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transaction.index');
});

// Invited onboarding
Route::get('invite/{portfolio}/{user}', InvitedOnboardingController::class)->name('invited_onboarding')->scopeBindings();

// Overwrites Jetstream routes
Route::get('/terms', [TermsOfServiceController::class, 'show'])->name('terms.show');
Route::get('/privacy', [PrivacyPolicyController::class, 'show'])->name('policy.show');

// social login routes
Route::get('auth/verify/{connected_account}', [ConnectedAccountController::class, 'verify'])->name('oauth.verify_connected_account');

Route::get('auth/{provider}', [ConnectedAccountController::class, 'redirectToProvider'])->name('oauth.redirect');
Route::get('auth/{provider}/callback', [ConnectedAccountController::class, 'handleProviderCallback'])->name('oauth.callback');
