<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/recipients')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::livewire('recipients', 'pages::recipients.index')->name('recipients.index');
    Route::livewire('templates', 'pages::templates.index')->name('templates.index');
    Route::livewire('send', 'pages::send.index')->name('send.index');
    Route::livewire('settings', 'pages::settings.index')->name('settings.index');
});

require __DIR__.'/settings.php';
