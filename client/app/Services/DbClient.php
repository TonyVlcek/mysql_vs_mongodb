<?php

namespace App\Services;

interface DbClient
{
	const EQ = '=';
	const LIKE = 'LIKE';

	public function seed(): void;

	public function read(array $filter): void;

	public function write(array $document): void;

	public function tear_down(): void;
}
