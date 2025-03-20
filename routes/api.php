<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\RadioStationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\V1\FavoriteStationController;

Route::middleware("auth:sanctum")->group(function () {
	Route::get("/user/session", [UserController::class, "session"]);
	Route::get("/user/details", [UserController::class, "details"]);

	// Add new favorite stations routes
	Route::get("/favorites", [FavoriteStationController::class, "index"]);
	Route::post("/favorites/{stationUuid}", [
		FavoriteStationController::class,
		"store"
	]);
	Route::get("/favorites/{stationUuid}", [
		FavoriteStationController::class,
		"show"
	]);
	Route::delete("/favorites/{stationUuid}", [
		FavoriteStationController::class,
		"destroy"
	]);
});

Route::middleware("throttle:10,1")->post("/register", [
	AuthController::class,
	"register"
]);
Route::middleware("throttle:10,1")->post("/login", [
	AuthController::class,
	"login"
]);
Route::middleware("throttle:10,1")
	->middleware("auth:sanctum")
	->group(function () {
		Route::post("/logout", [AuthController::class, "logout"]);
	});

Route::group(
	["prefix" => "v1", "namespace" => "App\Http\Controllers\Api\V1"],
	function () {
		Route::get("stations/random", [
			RadioStationController::class,
			"showRandom"
		]);
		Route::apiResource("stations", RadioStationController::class);
	}
);
