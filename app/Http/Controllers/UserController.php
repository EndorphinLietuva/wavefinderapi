<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
	public function session(Request $request)
	{
		$user = $request->user();
		return response()->json([
			"id" => $user->id,
			"username" => $user->username,
			"email" => $user->email
		]);
	}

	public function details(Request $request)
	{
		return response()->json($request->user());
	}
}
