<?php

namespace App\Services;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use PDO;
use PDOException;
use RuntimeException;

class Client implements DbClient
{

	private DbClient $client;
	private string $name;

	public function __construct(?string $target)
	{
		// If not set through env variable in DI, then set_target must be called prior to use
		if ($target !== null) {
			$this->set_target($target);
		}
	}

	public function set_target(string $target)
	{
		$this->client = match ($target) {
			'mongo' => new MongoClient(),
			'mysql' => new MySQLClient(),
			default => throw new RuntimeException(
				"Target {$target} is not supported. Pick 'mongo' or 'mysql'."
			),
		};

		$this->name = Strings::upper($target);
	}

	public function seed(): void
	{
		$this->client->seed();
	}

	public function read(array $filter): void
	{
		$this->client->read($filter);
	}

	/**
	 * @throws JsonException
	 */
	public function write(array $document): void
	{
		$this->client->write($document);
	}

	public function tear_down(): void
	{
		$this->client->tear_down();
	}

	public function get_name(): string
	{
		return $this->name;
	}
}
