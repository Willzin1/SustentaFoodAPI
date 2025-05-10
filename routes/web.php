<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Home da API, por favor utilize os endpoints descritos na documentação!']);
});
