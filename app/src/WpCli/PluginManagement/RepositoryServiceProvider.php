<?php

namespace App\WpCli\PluginManagement;

use WebImage\Config\Config;
use WebImage\Container\ServiceProvider\AbstractServiceProvider;

class RepositoryServiceProvider extends AbstractServiceProvider
{
	protected $provides = [Repository::class];

	public function register()
	{
		$this->getContainer()->add(Repository::class, function() {
			$config = $this->getApplication()->getConfig()->get('wp-cli', new Config());
			$pluginRepoDir = $config->get('pluginRepo');

			if (empty($pluginRepoDir)) $pluginRepoDir = $this->getApplication()->getProjectPath() . '/repos/plugins';

			return new Repository($pluginRepoDir);
		});
	}
}