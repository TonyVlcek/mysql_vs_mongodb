<?php declare(strict_types = 1);

namespace App\Commands;

use App\Services\DbClient;
use App\Services\DocumentGenerator;
use App\Services\MongoClient;
use App\Services\MySQLClient;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

final class WriteTestCommand extends Command
{

	/** @var string */
	protected static $defaultName = 'test:write';

	private MongoClient $mongoClient;
	private MySQLClient $mySQLClient;
	private DocumentGenerator $dg;

	public function __construct(
		MongoClient $mongoClient,
		MySQLClient $mySQLClient,
		DocumentGenerator $documentGenerator
	) {
		parent::__construct();

		$this->mongoClient = $mongoClient;
		$this->mySQLClient = $mySQLClient;
		$this->dg = $documentGenerator;
	}


	protected function configure(): void
	{
		$this->setName(self::$defaultName);
		$this->setDescription('Generates write traffic for the specified database');
		$this->addArgument('target', InputArgument::REQUIRED, 'Target database. `mongo` or `mysql` are the two allowed options');
		$this->addArgument('doc-type', InputArgument::REQUIRED, 'Type of the documents being inserted: small, deep, large, real ');
		$this->addArgument('duration', InputArgument::REQUIRED, 'Duration of the write experiment in seconds');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$target = $input->getArgument('target');
		$duration = $input->getArgument('duration');
		$duration_ms = $duration * 1_000;
		$doc_type = $input->getArgument('doc-type');
		if (!in_array($doc_type, ['small', 'deep', 'large', 'real', 'large_dynamic'], true)) {
			$output->writeln("<err>Document Type {$doc_type} is not supported.</err>");
		}

		$client = $this->getClient($target);
		$doc = $this->dg->{"get_$doc_type"}();


		$output->writeln("Running the WRITE test for {$target}, {$duration} s, inserting {$doc_type}.");

		$client->seed();
		$output->writeln('Seed complete');

		$records = 0;
		$elapsed = 0;

		Debugger::timer();
		do {
			$client->write($doc);

			$elapsed += Debugger::timer() * 1_000;
			$records++;
			$output->writeln("[{$elapsed}] Inserted record #{$records}", OutputInterface::VERBOSITY_DEBUG);
		} while ($elapsed < $duration_ms);

		$output->writeln("<info>Finished after {$duration} seconds. Inserted {$records}</info>");

		$client->tear_down();
		$output->writeln('Tear-down complete');

		return 0;
	}

	private function getClient(string $type): DbClient {
		if ($type === 'mongo') {
			echo "mongo";
			return $this->mongoClient;
		} elseif ($type === 'mysql') {
			return $this->mySQLClient;
		} else {
			throw new RuntimeException('Only `mongo` or `mysql` are accepted as types');
		}
	}

}
