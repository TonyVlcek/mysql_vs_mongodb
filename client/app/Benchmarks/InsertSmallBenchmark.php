<?php

namespace App\Benchmarks;

use App\Services\Client;
use App\Services\ConsoleOutput;
use App\Services\DocumentGenerator;
use Nette\Utils\JsonException;

final class InsertSmallBenchmark extends Benchmark
{
	public DocumentGenerator $dg;

	public function __construct(DocumentGenerator $dg, Client $client, ConsoleOutput $output)
	{
		parent::__construct($client, $output, 'insert_small');
		$this->dg = $dg;
	}

	protected function before(): void
	{
		// before not needed
	}

	protected function setup(): void
	{
		$this->client->seed();
	}

	/**
	 * @throws JsonException
	 */
	protected function do(): void
	{
		$this->client->write($this->dg->get_small());
	}

	protected function teardown(): void
	{
		$this->client->tear_down();
	}

	protected function after(): void
	{
		// after not needed
	}

}
