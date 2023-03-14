<?php

namespace App\Benchmarks;

use App\Services\Client;
use App\Services\ConsoleOutput;
use App\Services\DbClient;
use App\Services\DocumentGenerator;
use Nette\Utils\JsonException;

final class QueryLikeBenchmark extends QueryBenchmark
{

	public function __construct(Client $client, ConsoleOutput $output)
	{
		parent::__construct($client, $output, 'query_like');
	}

	protected function setup(): void
	{
		// no setup needed
	}

	protected function do(): void
	{
		$this->client->read(['one.two.three.four.five.six.text' => [DbClient::LIKE, $this->get_value_for_query('text_2')]]);
	}

	protected function teardown(): void
	{
		// no teardown needed
	}

}
