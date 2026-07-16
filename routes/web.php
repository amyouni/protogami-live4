<?php

use App\Services\TemplateService;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home.index')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard', ['templates' => app(TemplateService::class)->list()]);
    })->name('dashboard');
    Route::livewire('/workspace/{template?}', 'pages::proto.workspace')->name('workspace');
});

require __DIR__.'/settings.php';
