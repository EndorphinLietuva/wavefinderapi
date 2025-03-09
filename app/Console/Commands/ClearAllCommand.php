<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ClearAllCommand extends Command
{
	protected $signature = "clear:all";
	protected $description = "Clear all caches, config, routes, views, and other leftovers";

	public function handle()
	{
		// Clear application cache
		Artisan::call("cache:clear");
		$this->info("Application cache cleared!");

		// Clear config cache
		Artisan::call("config:clear");
		$this->info("Configuration cache cleared!");

		// Clear route cache
		Artisan::call("route:clear");
		$this->info("Route cache cleared!");

		// Clear view cache
		Artisan::call("view:clear");
		$this->info("Compiled views cleared!");

		// Clear compiled class file
		Artisan::call("clear-compiled");
		$this->info("Compiled services and packages removed!");

		// Clear optimized class loader
		Artisan::call("optimize:clear");
		$this->info("Optimized files cleared!");

		// Clear event cache
		if (array_key_exists("event:clear", Artisan::all())) {
			Artisan::call("event:clear");
			$this->info("Event cache cleared!");
		}

		// Clear expired password reset tokens
		Artisan::call("auth:clear-resets");
		$this->info("Expired password reset tokens cleared!");

		// Clear session files
		File::cleanDirectory(storage_path("framework/sessions"));
		$this->info("Session files cleared!");

		// Clear log files (optional)
		// File::put(storage_path("logs/laravel.log"), "");
		// $this->info("Log files cleared!");

		// Clear temporary uploads directory (optional)
		// File::cleanDirectory(storage_path('app/public/temp'));
		// $this->info('Temporary uploads cleared!');

		$this->info(
			"All caches, storage leftovers, and temporary files have been cleared!"
		);
	}
}
