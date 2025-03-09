<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\RadioStationController;
use App\Http\Controllers\Api\V1\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource("stations", RadioStationController::class);

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
Route::middleware("auth:sanctum")->group(function () {
	Route::post("/logout", [AuthController::class, "logout"]);
});

Route::group(
	["prefix" => "v1", "namespace" => "App\Http\Controllers\Api\V1"],
	function () {
		Route::apiResource("stations", RadioStationController::class);
	}
);
