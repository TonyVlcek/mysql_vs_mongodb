<?php

namespace App\Services;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use PDO;
use PDOException;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput
{

	private OutputInterface $output;

	/**
	 * Must be called prior to any other method being called.
	 */
	public function set_output(OutputInterface $output): void
	{
		$this->output = $output;
	}

	public function writeln(string $message, int $options = 0): void
	{
		$this->output->writeln($message, $options);
	}

	public function info(string $message): void
	{
		$this->output->writeln("<info>{$message}</info>");
	}

	public function debug(string $message): void
	{
		$this->output->writeln("{$message}", OutputInterface::VERBOSITY_DEBUG);
	}
}
