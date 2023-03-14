<?php

namespace App\Benchmarks;

use App\Services\Client;
use App\Services\ConsoleOutput;
use App\Services\DocumentGenerator;
use Nette\Utils\JsonException;

final class QueryUuidBenchmark extends QueryBenchmark
{

	public function __construct(Client $client, ConsoleOutput $output)
	{
		parent::__construct($client, $output, 'query_uuid');
	}

	protected function setup(): void
	{
		// no setup needed
	}

	protected function do(): void
	{
		$this->client->read(['uuid' => $this->get_value_for_query('uuid_1')]);
	}

	protected function teardown(): void
	{
		// no teardown needed
	}

}
