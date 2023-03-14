<?php

namespace App\Benchmarks;

use App\Services\Client;
use App\Services\ConsoleOutput;
use App\Services\DocumentGenerator;
use Nette\Utils\JsonException;

final class QueryDeepUuidBenchmark extends QueryBenchmark
{

	public function __construct(Client $client, ConsoleOutput $output)
	{
		parent::__construct($client, $output, 'query_deep_uuid');
	}

	protected function setup(): void
	{
		// no setup needed
	}

	protected function do(): void
	{
		$this->client->read(['one.two.three.four.five.six.uuid' => $this->get_value_for_query('uuid_2')]);
	}

	protected function teardown(): void
	{
		// no teardown needed
	}

}
