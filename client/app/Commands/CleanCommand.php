<?php declare(strict_types = 1);

namespace App\Commands;

use App\Services\MongoClient;
use App\Services\MySQLClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CleanCommand extends Command
{

	/** @var string */
	protected static $defaultName = 'clean';

	private MongoClient $mongoClient;

	private MySQLClient $mySQLClient;

	public function __construct(MongoClient $mongoClient, MySQLClient $mySQLClient)
	{
		parent::__construct();

		$this->mongoClient = $mongoClient;
		$this->mySQLClient = $mySQLClient;
	}

	protected function configure(): void
	{
		$this->setName(self::$defaultName);
		$this->setDescription('Runs teardown on both SUTs - deleting the databases');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->mongoClient->tear_down();
		$output->writeln('Mongo: Tear-down complete');

		$this->mySQLClient->tear_down();
		$output->writeln('MySQL: Tear-down complete');

		return 0;
	}

}
