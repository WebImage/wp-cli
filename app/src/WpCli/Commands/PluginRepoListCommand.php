<?php

namespace App\WpCli\Commands;

use App\WpCli\PluginManagement\Plugin;
use App\WpCli\PluginManagement\Repository;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WebImage\Application\AbstractCommand;

class PluginRepoListCommand extends AbstractCommand
{
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$repo = $this->getRootApplication()->getServiceManager()->get(Repository::class);
		$this->listPlugins($output, $repo);
	}

	protected function configure()
	{
		$this->setDescription('List plugins in repository');
	}

	protected function listPlugins(OutputInterface $output, Repository $repo)
	{
		$table = new Table($output);
		$table->setHeaders(['Plugin', 'Version(s)']);
		$table->addRows(array_map(function(Plugin $plugin) {
			return [$plugin->getName(), implode(', ', $plugin->getVersions())];
		}, $repo->getPlugins()));
		$table->render();
	}
}