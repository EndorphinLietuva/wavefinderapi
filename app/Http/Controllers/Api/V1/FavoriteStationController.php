<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\StationResource;
use App\Services\FavoriteStationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FavoriteStationController extends Controller
{
	protected FavoriteStationService $favoriteStationService;

	public function __construct(FavoriteStationService $favoriteStationService)
	{
		$this->favoriteStationService = $favoriteStationService;
	}

	/**
	 * Get all favorite stations for the authenticated user.
	 *
	 * @param Request $request
	 * @return AnonymousResourceCollection
	 */
	public function index(Request $request): AnonymousResourceCollection
	{
		$favorites = $this->favoriteStationService->getFavorites(
			$request->user()
		);
		return StationResource::collection($favorites);
	}

	/**
	 * Add a station to favorites.
	 *
	 * @param Request $request
	 * @param string $stationUuid
	 * @return Response
	 */
	public function store(Request $request, string $stationUuid): Response
	{
		$success = $this->favoriteStationService->addFavorite(
			$request->user(),
			$stationUuid
		);

		if ($success) {
			return response(
				[
					"message" => "Station added to favorites successfully"
				],
				201
			);
		}

		return response(
			[
				"message" =>
					"Station is already in favorites or could not be found"
			],
			422
		);
	}

	/**
	 * Check if a station is in the user's favorites.
	 *
	 * @param Request $request
	 * @param string $stationUuid
	 * @return Response
	 */
	public function show(Request $request, string $stationUuid): Response
	{
		$isFavorite = $this->favoriteStationService->isFavorite(
			$request->user(),
			$stationUuid
		);

		return response([
			"is_favorite" => $isFavorite
		]);
	}

	/**
	 * Remove a station from favorites.
	 *
	 * @param Request $request
	 * @param string $stationUuid
	 * @return Response
	 */
	public function destroy(Request $request, string $stationUuid): Response
	{
		$success = $this->favoriteStationService->removeFavorite(
			$request->user(),
			$stationUuid
		);

		if ($success) {
			return response([
				"message" => "Station removed from favorites successfully"
			]);
		}

		return response(
			[
				"message" => "Station was not in favorites"
			],
			404
		);
	}
}
