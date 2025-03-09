<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Services\RadioFetchService;

class FindDns extends Command
{
	protected $signature = "radio:find-dns";
	protected $description = "Find and cache the fastest Radio Browser DNS server";

	protected RadioFetchService $radioFetchService;

	public function __construct()
	{
		parent::__construct();
		$client = new Client(["timeout" => 60]);
		$this->radioFetchService = new RadioFetchService($client);
	}

	public function handle(): int
	{
		$fastest = $this->radioFetchService->findFastestDns();
		if ($fastest) {
			$this->info("Fastest server selected: {$fastest}");
			return 0;
		} else {
			$this->error("No live DNS server found.");
			return 1;
		}
	}
}
