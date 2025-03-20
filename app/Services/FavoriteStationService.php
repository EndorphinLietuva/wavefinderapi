<?php

namespace App\Services;

use App\Models\User;
use App\Models\RadioStation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class FavoriteStationService
{
	/**
	 * Get a user's favorite stations.
	 *
	 * @param User $user
	 * @return Collection
	 */
	public function getFavorites(User $user): Collection
	{
		return $user->favoriteStations()->get();
	}

	/**
	 * Add a station to a user's favorites.
	 *
	 * @param User $user
	 * @param string $stationUuid
	 * @return bool
	 */
	public function addFavorite(User $user, string $stationUuid): bool
	{
		try {
			$station = RadioStation::findOrFail($stationUuid);

			// Check if already a favorite to prevent duplicate entries
			if (
				!$user
					->favoriteStations()
					->where("station_uuid", $stationUuid)
					->exists()
			) {
				$user->favoriteStations()->attach($stationUuid);
				return true;
			}

			return false;
		} catch (\Exception $e) {
			Log::error("Failed to add favorite station: " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Remove a station from a user's favorites.
	 *
	 * @param User $user
	 * @param string $stationUuid
	 * @return bool
	 */
	public function removeFavorite(User $user, string $stationUuid): bool
	{
		try {
			return $user->favoriteStations()->detach($stationUuid) > 0;
		} catch (\Exception $e) {
			Log::error(
				"Failed to remove favorite station: " . $e->getMessage()
			);
			return false;
		}
	}

	/**
	 * Check if a station is in a user's favorites.
	 *
	 * @param User $user
	 * @param string $stationUuid
	 * @return bool
	 */
	public function isFavorite(User $user, string $stationUuid): bool
	{
		return $user
			->favoriteStations()
			->where("station_uuid", $stationUuid)
			->exists();
	}
}
