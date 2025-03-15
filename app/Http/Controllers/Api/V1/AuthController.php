<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class AuthController extends Controller
{
	public function register(Request $request)
	{
		$fields = $request->validate(
			[
				"username" =>
					"required|string|max:255|unique:users|regex:/^\S*$/u",
				"email" => "required|string|email|max:255|unique:users",
				"password" => "required|string|min:8|confirmed"
			],
			[
				"username.regex" => "The username must not contain spaces."
			]
		);
		// COULD BE UNSAFE, IDK
		$user = User::create($fields);
		$token = $user->createToken($request->username);
		return [
			"user" => $user,
			"token" => $token->plainTextToken
		];
	}

	public function login(Request $request)
	{
		$request->validate([
			"email" => "required|string|email|exists:users,email",
			"password" => "required|string"
		]);
		$user = User::where("email", $request->email)->first();
		if (!$user || !password_verify($request->password, $user->password)) {
			return [
				"errors" => [
					"email" => ["The provided credentials are incorrect."]
				]
			];
		}
		$token = $user->createToken($user->name);
		return [
			"user" => $user,
			"token" => $token->plainTextToken
		];
	}
	public function logout(Request $request)
	{
		$request->user()->tokens()->delete();
		return response()->json(["message" => "Logged out successfully"]);
	}
}
