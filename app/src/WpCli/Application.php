<?php

namespace App\WpCli;

use WebImage\Application\ConsoleApplication;

class Application extends ConsoleApplication
{
	protected function getSymfonyApplicationName(): string
	{
		return 'wp-cli';
	}

	protected function getSymfonyApplicationVersion(): string
	{
		return '1.0.0';
	}
}