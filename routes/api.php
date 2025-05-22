<?php

use App\Http\Controllers\ZohoController;
use Illuminate\Support\Facades\Route;


Route::post('/zoho/account', [ZohoController::class, 'store']);
