<?php

namespace App\WpCli\Commands;

use WebImage\Application\ApplicationInterface;

abstract class AbstractCommand extends \WebImage\Application\AbstractCommand
{
	protected function getRootApplication(): ApplicationInterface
	{
		return $this->getContainer()->get(ApplicationInterface::class);
	}
}