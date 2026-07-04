<?php

use App\Livewire\Homepage;
use App\Livewire\Dashboard;
use App\Livewire\Onboarding;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) return redirect()->route('home');
    return view('landing');
})->name('landing');

Route::get('/home', Homepage::class)->name('home');

Route::get('/onboarding', Onboarding::class)->middleware('auth')->name('onboarding');

Route::post('/onboarding/reset', function () {
    // Don't revoke onboarded status — the user stays free to navigate away mid-flow
    return redirect()->route('onboarding', ['recalculate' => 1]);
})->middleware('auth')->name('onboarding.reset');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/locale', function (\Illuminate\Http\Request $request) {
    $locale = $request->input('locale');
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('locale.switch');

require __DIR__.'/auth.php';
