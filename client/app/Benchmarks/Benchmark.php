<?php

namespace App\Benchmarks;

use App\Services\Client;
use App\Services\ConsoleOutput;
use Nette\Utils\FileSystem;
use Tracy\Debugger;

abstract class Benchmark
{
	protected Client $client;
	protected ConsoleOutput $output;
	protected int $dummy_rows;

	private static int $run_id = 0;
	private string $name;

	public function __construct(Client $client, ConsoleOutput $output, string $name)
	{
		$this->client = $client;
		$this->output = $output;
		$this->name = $name;
	}

	public function run(int $repeats, int $duration_s, ?int $dummy_rows = 0): void
	{
		$this->dummy_rows = $dummy_rows;

		$duration_ms = $duration_s * 1_000;

		$this->before();

		for ($i = 0; $i < $repeats; ++$i) {
			self::$run_id++;

			$operations = 0;
			$elapsed = 0;

			Debugger::timer();
			$this->setup();
			$setup_duration = Debugger::timer();

			Debugger::timer();
			do {
				$this->do(); //TODO: For 'length' benchamrs do might need to manipulate some secondary data store for additional data samples

				$elapsed += Debugger::timer() * 1_000;
				++$operations;
			} while ($elapsed < $duration_ms);

			$work_duration = $elapsed;

			$this->write_results((int) $setup_duration, (int) $work_duration, $operations);

			$this->teardown();
		}

		$this->after();
	}

	abstract protected function before(): void;

	abstract protected function setup(): void;

	abstract protected function do(): void;

	abstract protected function teardown(): void;

	abstract protected function after(): void;

	private function write_results(int $setup_duration, int $work_duration, int $operations): void
	{
		$result = self::$run_id. ", {$setup_duration}, {$work_duration}, {$operations}, {$this->dummy_rows}, {$this->name}";
		file_put_contents(__DIR__.'/../../results/results.csv', $result.PHP_EOL, FILE_APPEND | LOCK_EX);
		$this->output->info($result);
	}

}
