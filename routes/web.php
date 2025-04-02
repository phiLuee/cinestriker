<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;


Route::get('/', function () {
    return redirect()->route('movies.explorer');
})->name('index');


Volt::route('/reviews', 'reviews.index')->name('reviews.index');
// Volt::route('/reviews/{review}/edit', 'reviews.index')->name('reviews.edit');

Volt::route('/movies/explorer', 'movies.explorer')->name('movies.explorer');
// Volt::route('/movies/{imdbId}', 'movies.show')->name('movies.show');

Volt::route('/login', 'auth.login')->middleware(['guest'])->name('login');
Volt::route('/register', 'auth.register')->middleware(['guest'])->name('register');

Volt::route('/users', 'users.index')->middleware(['auth', 'role:admin'])->name('users.index');
Volt::route('/profile', 'profile.edit')->middleware('auth')->name('profile.edit');



// Logout-Route als klassische Route
Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->middleware('auth')->name('logout');


// Zeigt die Seite „Bitte E-Mail bestätigen“
Volt::route('/email/verify', 'auth.verify-email')
    ->middleware('auth')
    ->name('verification.notice');

// Verifiziert den Link in der E-Mail
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // markiert User als „verifiziert“
    return redirect()->route('index'); // Zielseite nach Verifizierung
})->middleware(['auth', 'signed'])->name('verification.verify');

// Erneut senden
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verifizierungs-E-Mail wurde erneut gesendet!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
