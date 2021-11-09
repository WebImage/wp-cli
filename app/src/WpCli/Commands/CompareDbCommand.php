<?php

namespace App\WpCli\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbConfig {
	public $host, $db, $user, $pass;

	/**
	 * DbConfig constructor.
	 * @param $host
	 * @param $db
	 * @param $user
	 * @param $pass
	 */
	public function __construct($host, $db, $user, $pass)
	{
		$this->host = $host;
		$this->db = $db;
		$this->user = $user;
		$this->pass = $pass;
	}
}

class CompareDbCommand extends AbstractCommand
{
	private $source, $target;

	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		$this->source = new DbConfig(
			$input->getOption('source-host'),
			$input->getOption('source-db'),
			$input->getOption('source-user'),
			$input->getOption('source-pass')
		);

		$this->target = new DbConfig(
			$input->getOption('target-host'),
			$input->getOption('target-db'),
			$input->getOption('target-user'),
			$input->getOption('target-pass')
		);

		$missing = [];
		foreach(['source-host', 'source-db', 'source-user', 'source-pass', 'target-host', 'target-db', 'target-user', 'target-pass'] as $required) {
			if (empty($input->getOption($required))) $missing[] = $required;
		}
		if (count($missing) > 0) throw new \RuntimeException('Missing required source and/or target db settings');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->compare($input, $output);
	}

	private function compare(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('Comparing: ' . $this->source->db . ' to ' . $this->target->db);
	}

	protected function configure()
	{
		$this->setDescription('Compare two installed WordPress databases')
			->addOption('source-host', null, InputOption::VALUE_REQUIRED, 'Source database host')
			->addOption('source-db', null, InputOption::VALUE_REQUIRED, 'Source database name')
			->addOption('source-user', null, InputOption::VALUE_REQUIRED, 'Source database user')
			->addOption('source-pass', null, InputOption::VALUE_REQUIRED, 'Source database password')
			// Destination
			->addOption('target-host', null, InputOption::VALUE_REQUIRED, 'Target database host')
			->addOption('target-db', null, InputOption::VALUE_REQUIRED, 'Target database name')
			->addOption('target-user', null, InputOption::VALUE_REQUIRED, 'Target database user')
			->addOption('target-pass', null, InputOption::VALUE_REQUIRED, 'Target database password')

		;
	}
}