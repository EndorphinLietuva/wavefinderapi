<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class FindDns extends Command
{
	protected $signature = "radio:find-dns";
	protected $description = "Find a working fastest Radio Browser DNS server";

	/**
	 * The base URL to get the list of available servers.
	 *
	 * @var string
	 */
	private string $baseUrl = "http://all.api.radio-browser.info/json/servers";

	/**
	 * Fallback URLs in case the base URL fails.
	 *
	 * @var array
	 */
	private array $fallbackUrls = [
		"https://de1.api.radio-browser.info",
		"https://fr1.api.radio-browser.info",
		"https://at1.api.radio-browser.info"
	];

	public function handle(): int
	{
		$client = new Client(["timeout" => 5]);
		$data = [];

		$this->info(
			"Fetching list of available servers from {$this->baseUrl} ..."
		);

		// Try getting the list from the base URL.
		try {
			$response = $client->get($this->baseUrl);
			$data = json_decode($response->getBody()->getContents(), true); // serveriu listas kuri parsiuncia

			// Build list of server URLs.
			$servers = [];
			foreach ($data as $item) {
				if (isset($item["name"])) {
					$servers[] = $item["name"];
				}
			}

			// remove duplicates
			$servers = array_unique($servers);
			if (empty($servers)) {
				$this->error("No server URLs found in the response.");
				throw new \InvalidArgumentException("servers are empty!");
			}

			$data = $this->getLiveServers($servers, $client);
		} catch (Exception) {
			$this->error(
				"Failed to fetch servers from base URL. Trying fallback URLs..."
			);

			$data = $this->getLiveServers($this->fallbackUrls, $client);
		}

		if (empty($data)) {
			$this->error("No API URL is live!");
			return 1; // 0 - gerai arba 1 - blogai ar komanda ivyko ar ne
		}

		// Find the fastest server among the live ones.
		$fastest = $this->findFastestServer($data, $client);
		if ($fastest) {
			$this->info("Fastest server selected: {$fastest}");
			Cache::forever("radio.fastest_dns", $fastest); // needs research
		} else {
			$this->error("Could not determine the fastest server.");
		}

		return 0;
	}

	/**
	 * Gets live servers using GET request.
	 *
	 * @param array $servers
	 * @param Client $client
	 * @return array
	 */
	private function getLiveServers($servers, $client): array
	{
		foreach ($servers as $server) {
			$this->info("Checking server: {$server}");
			if ($this->isServerLive($server, $client)) {
				$liveServers[] = $server;
				$this->info("Server {$server} is LIVE!");
			} else {
				$this->error("Server {$server} is OFFLINE.");
			}
		}

		return $liveServers;
	}

	/**
	 * Checks if a given server is live by sending a GET request.
	 *
	 * @param string $server
	 * @param Client $client
	 * @return bool
	 */
	private function isServerLive(string $server, Client $client): bool
	{
		try {
			$response = $client->get($server, [
				"timeout" => 5
			]);
			return $response->getStatusCode() === 200; // 200 - OK
		} catch (RequestException) {
			return false;
		}
	}

	/**
	 * Pings each live server to determine which one responds the fastest.
	 *
	 * @param array $servers
	 * @param Client $client
	 * @return string|null
	 */
	private function findFastestServer(array $servers, Client $client): ?string
	{
		$fastest = null;
		$minTime = INF;

		$this->info("Pinging live servers to determine response times...");
		foreach ($servers as $server) {
			$start = microtime(true);
			try {
				$client->get($server, ["timeout" => 3]);
				$responseTime = microtime(true) - $start;
				$this->info(
					"Server {$server} responded in " .
						round($responseTime * 1000, 2) .
						" ms"
				);

				if ($responseTime < $minTime) {
					// overwritinimo principu praeis cikle pro visus ir kazkuris vienas bus fastest
					$minTime = $responseTime;
					$fastest = $server;
				}
			} catch (RequestException $e) {
				$this->error("Failed to ping server: {$server}");
			}
		}

		return $fastest;
	}
}
