<?php

namespace App\Services;

use InvalidArgumentException;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

class MongoClient implements DbClient
{

	private Client $client;
	private Database $db;
	private Collection $collection;

	public function __construct()
	{
		$this->client = new Client('mongodb://user:password@sut-mongo:27017');
		$this->db = $this->client->selectDatabase('test');
		$this->collection = $this->db->selectCollection('collection');
	}

	public function seed(): void
	{
		// seeding mongo db is not needed, database and collection is created on demand
	}

	public function read(array $filter): void
	{
		$final = [];
		foreach ($filter as $key => $cond) {
			if (is_string($cond) || $cond[0] === DbClient::EQ) {
				$final[] = [$key => $cond[0] ?? $cond];
			} elseif ($cond[0] === DbClient::LIKE) {
				$final[] = [$key => ".*{$cond[1]}.*"];
			} else {
				throw new InvalidArgumentException('Unsupported operation read query comparator.');
			}
		}

		$this->collection->find($final);
	}

	public function write(array $document): void
	{
		$this->collection->insertOne($document);
	}

	public function tear_down(): void
	{
		$this->db->drop();
	}
}
