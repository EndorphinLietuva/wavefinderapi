<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RadioStation;
use App\Http\Resources\V1\StationResource;

class RadioStationController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		return StationResource::collection(RadioStation::paginate(10));
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(string $id)
	{
		return new StationResource(RadioStation::find($id));
	}

	/**
	 * Show a random station.
	 */
	public function showRandom()
	{
		return new StationResource(RadioStation::inRandomOrder()->first());
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(string $id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, string $id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(string $id)
	{
		//
	}
}
