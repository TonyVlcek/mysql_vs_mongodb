<?php

namespace App\Services;

use InvalidArgumentException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use PDO;
use PDOException;

class MySQLClient implements DbClient
{

	public static string $db = 'test_db';
	public static string $table = 'test_table';
	public static string $column = 'data';

	private PDO $client;

	public function __construct()
	{
		$options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
		try {
			$this->client = new PDO('mysql:host=sut-mysql;charset=utf8mb4', 'root', 'password', $options);
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage(), (int)$e->getCode());
		}
	}

	public function seed(): void
	{
		$this->client->query("CREATE DATABASE IF NOT EXISTS {$this::$db};");
		$this->client->query("USE {$this::$db};");
		$this->client->query("CREATE TABLE IF NOT EXISTS {$this::$table} ({$this::$column} JSON null);");
	}

	public function read(array $filter): void
	{
		$qs = $this::build_query($filter);

		$this->client->query($qs);
	}

	/**
	 * @throws JsonException
	 */
	public function write(array $document): void
	{
		$stmt = $this->client->prepare("INSERT INTO {$this::$table} ({$this::$column}) VALUES (:data);");
		$stmt->execute(['data' => Json::encode($document)]);
	}

	public function tear_down(): void
	{
		$this->client->query("DROP DATABASE {$this::$db}");
	}

	private function build_query(array $filter): string
	{
		$i = 0;
		$qs = '';
		foreach ($filter as $key => $cond) {
			if ($i++ > 0) {
				$qs .= ' AND ';
			}

			if (is_string($cond) || $cond[0] === DbClient::EQ) {
				$value = $cond[0] ?? $cond;
				$qs .= "{$this::$column}->'$.{$key}' = '{$value}'";
			} elseif ($cond[0] === DbClient::LIKE) {
				$qs .= "{$this::$column}->'$.{$key}' LIKE '%{$cond[1]}%'";
			} else {
				throw new InvalidArgumentException('Unsupported operation read query comparator.');
			}
		}

		return "SELECT * FROM {$this::$table} WHERE {$qs}";
	}
}
