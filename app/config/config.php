<?php

use App\WpCli\Commands\CoreCommand;
use App\WpCli\Commands\PluginRepoListCommand;
use App\WpCli\Commands\PluginRepoAddCommand;
use App\WpCli\Commands\ListPluginsCommand;
use App\WpCli\Commands\CompareDbCommand;
use WebImage\WpCli\HackScanner\CheckRedirectCommand;

return [
	'wp-cli' => [
		'coreRepo' => __DIR__ . '/../repos/core',
		'pluginRepo' => __DIR__ . '/../repos/plugins'
	],
	'plugins' => [
		WebImage\WpCli\HackScanner\Plugin::class
	],
	'console' => [
		'commands' => [
			'core' => CoreCommand::class,
			'pluginrepo:list' => PluginRepoListCommand::class,
			'pluginrepo:add' => PluginRepoAddCommand::class,
			'check-redirect' => CheckRedirectCommand::class,
			'plugins:list' => ListPluginsCommand::class,
			'db:compare' => CompareDbCommand::class
		]
	],
	'serviceManager' => [
		'providers' => [\App\WpCli\PluginManagement\RepositoryServiceProvider::class]
	]
];