<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentFileController;

// MARCOFIC Nederlandse Landing Page
Route::get('/', function () {
    return view('landing-nl');
})->name('home');

// Redirect to appropriate panel based on user role
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if (!$user) {
        return redirect('/admin/login');
    }
    
    // If user has client_id, redirect to client portal
    if ($user->client_id) {
        return redirect('/klanten');
    }
    
    // Otherwise, admin portal
    return redirect('/admin');
})->name('dashboard');

// Secure document file routes (must be authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('/documents/{document}/file', [DocumentFileController::class, 'serve'])
        ->name('documents.file');
    Route::get('/documents/{document}/download', [DocumentFileController::class, 'download'])
        ->name('documents.download');
    Route::get('/invoices/{document}/pdf', [DocumentFileController::class, 'downloadInvoicePdf'])
        ->name('invoices.pdf');
});
