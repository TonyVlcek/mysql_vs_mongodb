<?php

namespace App\Benchmarks;

use App\Services\Client;
use App\Services\ConsoleOutput;
use App\Services\DocumentGenerator;
use Faker\Factory;
use Nette\Utils\JsonException;

abstract class QueryBenchmark extends Benchmark
{

	private array $existing_values;

	public function __construct(Client $client, ConsoleOutput $output, string $name)
	{
		parent::__construct($client, $output, $name);
	}

	protected function before(): void
	{
		$this->client->seed();
		$this->dummy_data($this->dummy_rows);

		$this->output->info('Dummy data inserted.');
	}

	protected function after(): void
	{
		$this->existing_values = [];
		$this->client->tear_down();

		$this->output->info('Cleanup after query benchmark done.');
	}

	protected function get_value_for_query(string $key): mixed
	{
		$f = Factory::create();

		return $f->randomElement($this->existing_values[$key]);
	}

	private function dummy_data(int $docs): void
	{
		$f = Factory::create();

		for ($i=0; $i < $docs; ++$i) {
			$uuid_1 = $f->uuid;
			$uuid_2 = $f->uuid;
			$text_1 = $f->paragraph;
			$text_2 = $f->paragraph;

			$v2 = $f->word;

			//TODO: Only a trivial example for now
			$this->client->write([
				'uuid' => $uuid_1,
				'text' => $text_1,
				'one' => [
					'two' => [
						'three' => [
							'four' => [
								'five' => [
									'six' => [
										'uuid' => $uuid_2,
										'text' => $text_2,
									]
								]
							]
						]
					],
				],
			]);

			// Assign random existing values to the pool from which queries will be made
			if (rand(0, 100) === 1) {
				$this->existing_values['uuid_1'][] = $uuid_1;
				$this->existing_values['uuid_2'][] = $uuid_2;
				$this->existing_values['text_1'][] = $this::rand_substr($text_1);
				$this->existing_values['text_2'][] = $this::rand_substr($text_2);
			}
		}
	}

	private static function rand_substr(string $str): string
	{
		$len = strlen($str);
		$start = rand(0,  $len - 1);
		$end = rand($start, $len);

		return substr($str, $start, $end);
	}

}
