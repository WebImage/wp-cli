<?php

namespace App\WpCli\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WebImage\Application\AbstractCommand;

class CoreCommand extends AbstractCommand
{
	protected function execute(InputInterface $input, OutputInterface $output)
	{
	}

	protected function configure()
	{
		$this->setDescription('Work with WordPress core files');
	}
}