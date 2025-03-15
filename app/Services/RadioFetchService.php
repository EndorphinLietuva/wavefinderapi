<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\RadioStation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class RadioFetchService
{
	protected Client $client;
	protected string $baseUrl = "http://all.api.radio-browser.info/json/servers";
	protected array $fallbackUrls = [
		"https://de1.api.radio-browser.info",
		"https://fr1.api.radio-browser.info",
		"https://at1.api.radio-browser.info"
	];
	protected $ignoredStations;

	public function __construct(Client $client)
	{
		$this->client = $client;
		$this->ignoredStations = collect();
	}

	/**
	 * Get the stations that were ignored during the seeding process.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getIgnoredStations()
	{
		return $this->ignoredStations;
	}

	/**
	 * Fetch the total number of radio stations from the fastest DNS server.
	 *
	 * @param int $limit
	 * @return array
	 */
	public function getTotalStations()
	{
		if (!$this->checkDns()) {
			return [];
		}
		$response = $this->client->get(
			Cache::get("radio.fastest_dns") . "/json/stats",
			["http_errors" => false]
		);
		$stats = json_decode($response->getBody()->getContents(), true);
		return $stats["stations"] ?? 0;
	}

	/**
	 * Check if the fastest DNS server is cached.
	 *
	 * @return bool
	 */
	private function checkDns(): bool
	{
		$result = Cache::get("radio.fastest_dns");
		if (!$result) {
			Log::error(
				"No fastest DNS server found. Please run findFastestDns() first."
			);
		}
		return !empty($result);
	}

	/**
	 * Find the fastest live DNS server and cache the result.
	 *
	 * @return string|null
	 */
	public function findFastestDns(): ?string
	{
		$servers = [];
		try {
			$response = $this->client->get($this->baseUrl, ["timeout" => 5]);
			$data = json_decode($response->getBody()->getContents(), true);

			foreach ($data as $item) {
				if (isset($item["name"])) {
					$servers[] = $item["name"];
				}
			}

			$servers = array_unique($servers);
			if (empty($servers)) {
				Log::warning(
					"No servers found in the response from the DNS API. Using fallback URLs."
				);
				$liveServers = $this->getLiveServers($this->fallbackUrls);
			}

			$liveServers = $this->getLiveServers($servers);
		} catch (Exception $e) {
			Log::error("Failed to fetch servers from the DNS API.", [
				"exception" => $e
			]);
			throw $e;
		}

		if (empty($liveServers)) {
			return null;
		}

		$fastest = $this->findFastestServer($liveServers);
		if ($fastest) {
			Cache::forever("radio.fastest_dns", $fastest);
		}
		return $fastest;
	}

	/**
	 * Check an array of server URLs and return only the live ones.
	 *
	 * @param array $servers
	 * @return array
	 */
	protected function getLiveServers(array $servers): array
	{
		$liveServers = [];
		foreach ($servers as $server) {
			if ($this->isServerLive($server)) {
				$liveServers[] = $server;
			}
		}
		return $liveServers;
	}

	/**
	 * Determine if a given server is live.
	 *
	 * @param string $server
	 * @return bool
	 */
	protected function isServerLive(string $server): bool
	{
		try {
			$response = $this->client->get($server, ["timeout" => 5]);
			return $response->getStatusCode() === 200;
		} catch (RequestException $e) {
			return false;
		}
	}

	/**
	 * Ping each live server to determine which responds the fastest.
	 *
	 * @param array $servers
	 * @return string|null
	 */
	protected function findFastestServer(array $servers): ?string
	{
		$fastest = null;
		$minTime = INF;
		foreach ($servers as $server) {
			$start = microtime(true);
			try {
				$this->client->get($server, ["timeout" => 5]);
				$responseTime = microtime(true) - $start;
				if ($responseTime < $minTime) {
					$minTime = $responseTime;
					$fastest = $server;
				}
			} catch (RequestException $e) {
				// Skip servers that fail to respond in time
			}
		}
		return $fastest;
	}

	/**
	 * Seed radio stations from the fastest DNS API.
	 *
	 * @param int $chunkSize
	 * @param int $limit  If set to a non-zero value, limits the number of inserted stations.
	 * @return int Total number of stations inserted.
	 */
	public function seedStations(
		int $chunkSize = 10000,
		int $limit = 0,
		$progress
	): int {
		if (!$this->checkDns()) {
			return 0;
		}
		$apiUrl = Cache::get("radio.fastest_dns") . "/json/stations";
		$offset = 0;
		$totalInserted = 0;

		while (true) {
			$response = $this->client->get($apiUrl, [
				"query" => [
					"limit" =>
						$limit > 0 && $limit < $chunkSize ? $limit : $chunkSize,
					"offset" => $offset,
					"hidebroken" => "true"
				]
			]);
			$stations = json_decode($response->getBody()->getContents(), true);
			if (empty($stations)) {
				break;
			}

			$filtered = $this->processBatch($stations);
			$this->insertBatch($filtered);
			$processedCount = count($filtered);
			$totalInserted += $processedCount;

			if ($limit > 0 && $totalInserted >= $limit) {
				// Checks if the limit is reached.
				break;
			}
			if ($limit > 0 && $totalInserted + count($stations) >= $limit) {
				// Checks if inserted stations + current batch will exceed the limit.
				// If true, it adjusts the $chunkSize to ensure that the next batch does not exceed the limit.
				// e. g. limit = 100, total inserted = 90, current batch = 50
				// 		 next batch is set to 10
				$chunkSize = $limit - $totalInserted;
			}
			if (count($stations) < $chunkSize) {
				// on the "last page", because it couldn't fetch the full chunk.
				break;
			}

			$offset += count($stations);

			if (is_callable($progress)) {
				$progress($totalInserted);
			}
		}

		DB::beginTransaction();
		try {
			DB::statement("TRUNCATE TABLE radio_stations");
			DB::statement(
				"INSERT INTO radio_stations SELECT * FROM radio_stations_staging"
			);

			DB::statement("TRUNCATE TABLE radio_stations_staging");

			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();
			Log::error(
				"Failed to copy data and rename tables: " . $e->getMessage()
			);
			return 0;
		}

		return $totalInserted;
	}

	/**
	 * Process and filter a batch of stations.
	 *
	 * @param array $stations
	 * @return array
	 */
	protected function processBatch(array $stations): array
	{
		return collect($stations)
			->filter(fn($station) => $this->applyFilters($station))
			->map(function ($station) {
				return [
					"change_uuid" => $station["changeuuid"],
					"station_uuid" => $station["stationuuid"],
					"server_uuid" => $station["serveruuid"],
					"name" => trim(str_replace("\t", "", $station["name"])),
					"url" => $station["url"],
					"url_resolved" => $station["url_resolved"],
					"home_page" => $station["homepage"],
					"favicon" => $station["favicon"],
					"tags" => $station["tags"],
					"country_code" => strtoupper($station["countrycode"]),
					"iso_3166_2" => $station["iso_3166_2"],
					"state" => $station["state"],
					"language" => $station["language"],
					"language_codes" => $station["languagecodes"],
					"votes" => $station["votes"],
					"last_change_time" => Carbon::parse(
						$station["lastchangetime"]
					),
					"codec" => $station["codec"],
					"bit_rate" => $station["bitrate"],
					"hls" => $station["hls"],
					"last_check_ok" => $station["lastcheckok"],
					"last_check_time" => Carbon::parse(
						$station["lastchecktime"]
					),
					"last_check_ok_time" => Carbon::parse(
						$station["lastcheckoktime"]
					),
					"last_local_check_time" => Carbon::parse(
						$station["lastlocalchecktime"]
					),
					"click_timestamp" => Carbon::parse(
						$station["clicktimestamp"]
					),
					"click_count" => $station["clickcount"],
					"click_trend" => $station["clicktrend"],
					"ssl_error" => $station["ssl_error"],
					"geo_lat" => $station["geo_lat"],
					"geo_long" => $station["geo_long"],
					"geo_distance" => $station["geo_distance"],
					"has_extended_info" => $station["has_extended_info"]
				];
			})
			->toArray();
	}

	/**
	 * Insert a batch of stations into the database.
	 *
	 * @param array $stations
	 */
	protected function insertBatch(array $stations)
	{
		if (empty($stations)) {
			throw new \InvalidArgumentException(
				"Stations array cannot be empty."
			);
		}
		DB::beginTransaction();

		foreach ($stations as $station) {
			try {
				DB::table("radio_stations_staging")->insert($station);
				DB::commit();
			} catch (Exception $e) {
				DB::rollBack();
				Log::error("Batch insert failed: " . $e->getMessage());
				throw $e;
			}
		}
	}

	/**
	 * Apply your desired filters to a station.
	 *
	 * @param array $station
	 * @return bool
	 */
	protected function applyFilters(array $station): bool
	{
		$result =
			$this->filterByName($station) &&
			$this->filterByFieldLength($station) &&
			$this->filterByUrl($station);
		if (!$result) {
			$this->ignoredStations->push($station);
		}
		return $result;
	}

	/**
	 * Filter: Only include stations that have fields with length <= 2048.
	 *
	 * @param array $station
	 * @return bool
	 */
	protected function filterByFieldLength(array $station): bool
	{
		foreach ($station as $field) {
			if (is_string($field) && strlen($field) > 2048) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Filter: Only include stations that have non white space names.
	 *
	 * @param array $station
	 * @return bool
	 */
	protected function filterByName(array $station): bool
	{
		return !empty(trim($station["name"]));
	}

	/**
	 * Filter: Only include stations that have non white space urls.
	 *
	 * @param array $station
	 * @return bool
	 */
	protected function filterByUrl(array $station): bool
	{
		return !empty(trim($station["url"])) &&
			!empty(trim($station["url_resolved"]));
	}
}
