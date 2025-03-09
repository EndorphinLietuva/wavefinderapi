<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupDb extends Command
{
	protected $signature = "radio:setup";
	protected $description = "Run migration and seed stations";

	public function handle()
	{
		$this->call("migrate:fresh");
		$this->call("radio:find-dns");
		$this->call("radio:seed-stations");
		$this->info("All commands executed!");
	}
}
