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

final class RunAllCommand extends Command
{

	/** @var string */
	protected static $defaultName = 'run:all';

	private Client $client;
	private ConsoleOutput $output;
	private InsertSmallBenchmark $insertSmallBenchmark;
	private InsertDeepBenchmark $insertDeepBenchmark;
	private InsertLongBenchmark $insertLongBenchmark;
	private QueryUuidBenchmark $queryUuidBenchmark;
	private QueryDeepUuidBenchmark $queryDeepUuidBenchmark;
	private QueryLikeBenchmark $queryLikeBenchmark;


	public function __construct(
		Client $client,
		ConsoleOutput $output,
		InsertSmallBenchmark $insertSmallBenchmark,
		InsertDeepBenchmark $insertDeepBenchmark,
		InsertLongBenchmark $insertLongBenchmark,
		QueryUuidBenchmark $queryUuidBenchmark,
		QueryDeepUuidBenchmark $queryDeepUuidBenchmark,
		QueryLikeBenchmark $queryLikeBenchmark,
	) {
		parent::__construct();
		$this->client = $client;
		$this->output = $output;
		$this->insertSmallBenchmark = $insertSmallBenchmark;
		$this->insertDeepBenchmark = $insertDeepBenchmark;
		$this->insertLongBenchmark = $insertLongBenchmark;
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

		$results_path = __DIR__.'/../../results/results.csv';
		file_put_contents(
			$results_path,
			'run_id, setup_duration, work_duration, operations, dummy_rows, benchmark_name'.PHP_EOL,
			LOCK_EX
		);

		// INSERT BENCHMARKS
		$this->insertSmallBenchmark->run(5, 2);
		$this->insertSmallBenchmark->run(10, 10);

		$this->insertDeepBenchmark->run(5, 2);
		$this->insertDeepBenchmark->run(10, 10);

		$this->insertLongBenchmark->run(5, 2);
		$this->insertLongBenchmark->run(10, 10);

		// QUERY BENCHMARKS
		$this->queryUuidBenchmark->run(5, 5, 1_000);
		$this->queryUuidBenchmark->run(5, 5, 10_000);
		$this->queryUuidBenchmark->run(5, 5, 100_000);

		$this->queryDeepUuidBenchmark->run(5, 5, 10_000);
		$this->queryLikeBenchmark->run(5, 5, 10_000);

		$storage = new StorageClient(['projectId' => 'tu-csb-test-1']);
		$bucket = $storage->bucket('tu-csb-vlcek-results');

		$bucket->upload(fopen($results_path, 'r'), [
			'name' => (new DateTime())->format('md_h-i-s')."__{$this->client->get_name()}__results.csv",
		]);

		return 0;
	}
}
