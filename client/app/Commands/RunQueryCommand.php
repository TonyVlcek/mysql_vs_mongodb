<?php declare(strict_types = 1);

namespace App\Commands;

use App\Benchmarks\InsertDeepBenchmark;
use App\Benchmarks\InsertLongBenchmark;
use App\Benchmarks\InsertSmallBenchmark;
use App\Benchmarks\QueryDeepUuidBenchmark;
use App\Benchmarks\QueryLikeBenchmark;
use App\Benchmarks\QueryUuidBenchmark;
use App\Services\Client;
use App\Services\ConsoleOutput;
use DateTime;
use Google\Cloud\Storage\StorageClient;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class RunQueryCommand extends Command
{

	/** @var string */
	protected static $defaultName = 'run:query';

	private Client $client;
	private ConsoleOutput $output;

	private QueryUuidBenchmark $queryUuidBenchmark;
	private QueryDeepUuidBenchmark $queryDeepUuidBenchmark;
	private QueryLikeBenchmark $queryLikeBenchmark;


	public function __construct(
		Client $client,
		ConsoleOutput $output,
		QueryUuidBenchmark $queryUuidBenchmark,
		QueryDeepUuidBenchmark $queryDeepUuidBenchmark,
		QueryLikeBenchmark $queryLikeBenchmark
	) {
		parent::__construct();
		$this->client = $client;
		$this->output = $output;

		$this->queryUuidBenchmark = $queryUuidBenchmark;
		$this->queryDeepUuidBenchmark = $queryDeepUuidBenchmark;
		$this->queryLikeBenchmark = $queryLikeBenchmark;
	}


	protected function configure(): void
	{
		$this->setName(self::$defaultName);
		$this->setDescription('Generates write traffic for the specified database');
		$this->addOption('target', 't', InputOption::VALUE_OPTIONAL, 'Overwrite target.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		if (($target = $input->getOption('target')) !== null) {
			$this->client->set_target($target);
		}

		$this->output->set_output($output);

		$output->writeln("<info>Running benchmarks for {$this->client->get_name()}</info>");

		$this->queryUuidBenchmark->run(10, 2, 1_000);
		$this->queryUuidBenchmark->run(10, 2, 10_000);
		$this->queryUuidBenchmark->run(10, 2, 100_000);

		$this->queryDeepUuidBenchmark->run(10, 3, 10_000);
		$this->queryLikeBenchmark->run(10, 3, 10_000);

		return 0;
	}

}
