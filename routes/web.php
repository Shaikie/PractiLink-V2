<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return (auth('web')->check() || auth('students')->check())
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
