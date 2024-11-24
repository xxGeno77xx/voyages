<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {

    dd(Carbon::parse("13/09/2024"));

    return view('welcome');
});
