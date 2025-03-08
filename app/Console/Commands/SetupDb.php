<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupDb extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = "radio:setup";

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Run migration and seed stations";

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$this->call("migrate:fresh");
		$this->call("radio:find-dns");
		$this->call("radio:seed-stations");

		$this->info("All commands executed!");
	}
}
