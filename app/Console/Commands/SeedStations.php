<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RadioFetchService;
use GuzzleHttp\Client;

class SeedStations extends Command
{
	protected $signature = "radio:seed-stations";
	protected $description = "Seed radio stations from API";

	protected RadioFetchService $radioFetchService;

	public function __construct()
	{
		parent::__construct();
		$client = new Client(["timeout" => 60]);
		$this->radioFetchService = new RadioFetchService($client);
	}

	public function handle(): int
	{
		try {
			// Settings
			$chunkSize = config("app.debug") ? 10000 : 10000;
			$limit = config("app.debug") ? 0 : 0;

			$totalStations = $this->radioFetchService->getTotalStations();
			$typeOfLimit = $limit > 0 ? $limit : $totalStations;
			$bar = $this->output->createProgressBar($typeOfLimit);
			$this->info(
				"\nAttempting to insert: {$typeOfLimit}/{$totalStations} stations by chunks of {$chunkSize}."
			);

			$bar->start();
			$total = $this->radioFetchService->seedStations(
				chunkSize: $chunkSize,
				limit: $limit,
				progress: function ($totalInserted) use ($bar) {
					$bar->setProgress($totalInserted);
				}
			);
			$bar->finish();

			$this->info("\nSeeding completed.");
			$this->info("\nTotal stations inserted: {$total}/{$typeOfLimit}");
			$this->info(
				"\nFiltered out {$this->radioFetchService->getIgnoredStations()->count()} stations.\n"
			);

			return 0;
		} catch (\Exception $e) {
			$this->error("Error seeding stations: " . $e->getMessage());
			return 1;
		}
	}
}
