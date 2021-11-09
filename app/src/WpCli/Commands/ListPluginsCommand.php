<?php

namespace App\WpCli\Commands;

use App\WpCli\PluginManagement\Repository;
use App\WpCli\Utils\LocalInstallUtils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListPluginsCommand extends AbstractCommand
{
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$path = $input->getArgument('path');
		if (empty($path)) $path = $this->getCurrentWorkingDirectory(); // Default to current directory

		if (!LocalInstallUtils::isWordPressInstall($path)) {
			throw new \Exception('Must run from WordPress directory or provided a valid WordPress root directory path as the first argument');
		}

		/** @var Repository $repo */
		$repo = $this->getRootApplication()->getServiceManager()->get(Repository::class);

		$plugins = LocalInstallUtils::getPlugins($repo, $path);

		$table = new Table($output);
		$table->setHeaders(['Name', 'Version', 'Available', 'In Repo', 'Upgrade Available']);

		foreach($plugins as $plugin) {
			$table->addRow([
				$plugin->getName(),
				$plugin->getVersion(),
				implode(', ', $plugin->getAvailableVersions()),
				$plugin->isInRepo() ? 'Yes' : 'No',
				$plugin->isUpgradeAvailable() ? 'Yes' : 'No'
			]);
		}

		$table->render();
	}

	protected function configure()
	{
		$this->setDescription('List plugins in locally installed WP installation')
			->addArgument('path', InputArgument::OPTIONAL, 'The path to the WordPress root folder');
	}
}