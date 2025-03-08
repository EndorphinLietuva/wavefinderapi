<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\RadioStation;
use Carbon\Carbon;

// ! VELIAU PADARYTI SERVICE NORMALIAI
class SeedStations extends Command
{
	protected $signature = "radio:seed-stations";
	protected $description = "Seed radio stations from API";

	protected $apiUrl;
	protected $client;

	public function handle()
	{
		$this->apiUrl = Cache::get("radio.fastest_dns");
		if (!$this->apiUrl) {
			$this->error(
				'No fastest DNS server found. Run the "radio:find-dns" command first.'
			);
			return 1;
		}

		$this->client = new Client(["timeout" => config("app.debug") ? 30 : 5]);
		$this->apiUrl .= "/json/stations";

		$this->info("Starting seeding process...");

		// ! padaryti kad nedropintu o neaktyvias archyvuotu (sugalvoti logika)
		$this->truncateTable();
		$this->seedStations();

		$this->info("\nSeeding completed successfully.");
		return 0;
	}

	protected function truncateTable()
	{
		$this->info("Truncating table...");
		DB::table("radio_stations")->truncate();
	}

	protected function seedStations()
	{
		$offset = 0;
		$totalInserted = 0;
		$chunkSize = 20;
		$progressBar = $this->output->createProgressBar();

		while (true) {
			try {
				$response = $this->client->get($this->apiUrl, [
					"query" => [
						"limit" => $chunkSize,
						"offset" => $offset,
						"hidebroken" => "true"
					]
				]);

				$stations = json_decode($response->getBody(), true);

				if (empty($stations)) {
					$this->error("No stations found.");
					break;
				}

				$filtered = $this->processBatch($stations);
				$this->insertBatch($filtered);

				$totalInserted += count($filtered);
				$progressBar->advance(count($filtered));

				// Break conditions
				if (count($stations) < $chunkSize) {
					break;
				}
				if (config("app.debug") && $totalInserted >= $chunkSize) {
					break;
				}

				$offset += $chunkSize;
			} catch (\Exception $e) {
				$this->error("\nError: " . $e->getMessage());
				break;
			}
		}

		$progressBar->finish();
		$this->info("\nTotal stations inserted: " . $totalInserted);
	}

	protected function processBatch(array $stations): array
	{
		return collect($stations)
			->filter(fn($station) => $this->applyFilters($station))
			->map(function ($station) {
				return [
					"changeuuid" => $station["changeuuid"],
					"stationuuid" => $station["stationuuid"],
					"serveruuid" => $station["serveruuid"],
					"name" => $station["name"],
					"url" => $station["url"],
					"url_resolved" => $station["url_resolved"],
					"homepage" => $station["homepage"],
					"favicon" => $station["favicon"],
					"tags" => $station["tags"],
					"country" => $station["country"],
					"countrycode" => $station["countrycode"],
					"iso_3166_2" => $station["iso_3166_2"],
					"state" => $station["state"],
					"language" => $station["language"],
					"languagecodes" => $station["languagecodes"],
					"votes" => $station["votes"],
					"lastchangetime" => Carbon::parse(
						$station["lastchangetime"]
					),
					"codec" => $station["codec"],
					"bitrate" => $station["bitrate"],
					"hls" => $station["hls"],
					"lastcheckok" => $station["lastcheckok"],
					"lastchecktime" => Carbon::parse($station["lastchecktime"]),
					"lastcheckoktime" => Carbon::parse(
						$station["lastcheckoktime"]
					),
					"lastlocalchecktime" => Carbon::parse(
						$station["lastlocalchecktime"]
					),
					"clicktimestamp" => Carbon::parse(
						$station["clicktimestamp"]
					),
					"clickcount" => $station["clickcount"],
					"clicktrend" => $station["clicktrend"],
					"ssl_error" => $station["ssl_error"],
					"geo_lat" => $station["geo_lat"],
					"geo_long" => $station["geo_long"],
					"geo_distance" => $station["geo_distance"],
					"has_extended_info" => $station["has_extended_info"]
				];
			})
			->toArray();
	}

	protected function insertBatch(array $stations)
	{
		if (empty($stations)) {
			return;
		}

		try {
			DB::beginTransaction();
			RadioStation::insert($stations);
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			$this->error("Batch insert failed: " . $e->getMessage());
		}
	}

	/**
	 * Apply all filters to a station.
	 *
	 * @param array $station
	 * @return bool
	 */
	protected function applyFilters(array $station): bool
	{
		return true;
		// return $this->filterByFavicon($station) && $this->filterByCountry($station);
	}

	/**
	 * Filter: Only include stations that have a favicon.
	 *
	 * @param array $station
	 * @return bool
	 */
	protected function filterByFavicon(array $station): bool
	{
		return !empty($station["favicon"]);
	}

	/**
	 * Filter: Only include stations from the United States.
	 *
	 * @param array $station
	 * @return bool
	 */
	protected function filterByCountry(array $station): bool
	{
		return isset($station["country"]) &&
			$station["country"] === "United States";
	}
}
