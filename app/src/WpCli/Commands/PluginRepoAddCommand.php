<?php

namespace App\WpCli\Commands;

use App\WpCli\FileUtils;
use App\WpCli\PluginManagement\Plugin;
use App\WpCli\PluginManagement\Repository;
use App\WpCli\Utils\LocalInstallUtils;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use WebImage\Application\AbstractCommand;

class PluginRepoAddCommand extends AbstractCommand
{
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$repo = new Repository($this->getRootApplication()->getProjectPath() . '/repos/plugins');
		$this->addPlugin($input, $output, $repo);
	}

	protected function configure()
	{
		$this->setDescription('Add a plugin to the repository')
			->addArgument('name', InputArgument::OPTIONAL, 'The plugin-key to be added')
			->addArgument('version', InputArgument::OPTIONAL, 'The plugin version (uses latest by default)');
	}

	protected function addPlugin(InputInterface $input, OutputInterface $output, Repository $repo)
	{
		$name = $input->getArgument('name');

		if ($name) {
			$this->addPluginVersion($input, $output, $repo, $name);
		} else {

			$pluginQuestion = new Question('Plugin (key): ');
			$plugin = $this->getQuestionHelper()->ask($input, $output, $pluginQuestion);

			if (empty($plugin)) die(__FILE__ . ':' . __LINE__ . PHP_EOL);

			$output->writeln('Will install plugin: ' . $plugin);
		}
	}

	protected function addPluginVersion(InputInterface $input, OutputInterface $output, Repository $repo, string $plugin)
	{
		$versions = $repo->getPluginVersions($plugin);
		if (count($versions) > 0) $output->writeln('Current versions ['.$plugin.']: ' . implode(', ', $versions));

		$version = $input->getArgument('version');

		if ($version == 'latest') {
			$version = null;
		} else {
			$versionQuestion = new Question('Version (blank for "latest"): ', null);
			$version = $this->getQuestionHelper()->ask($input, $output, $versionQuestion);
			$output->writeln('Version: ' . $version);
		}

		$this->downloadPlugin($output, $repo, $plugin, empty($version) ? null : $version);
	}

	protected function downloadPlugin(OutputInterface $output, Repository $repo, string $plugin, string $version=null)
	{
		if ($repo->shouldSkipPluginDownload($plugin)) throw new Exception('This plugin is marked for skip. You should be calling should_skip_plugin_download($plugin) first');

		$url = 'https://downloads.wordpress.org/plugin/%s.zip';
		$fileKey = $plugin;
		if (null !== $version) $fileKey .= '.' . $version;

		$downloadDir = $repo->getPath() . '/_tmp';
		if (!file_exists($downloadDir)) mkdir($downloadDir);

		$downloadUrl = sprintf($url, $fileKey);
		$downloadPath = $downloadDir . '/' . basename($downloadUrl);

		$output->writeln('URL: ' . $downloadUrl);
		$output->writeln('Download path: ' . $downloadPath . ' - ' . (file_exists($downloadPath) ? 'Already exists' : 'Downloading'));

		if (!file_exists($downloadPath)) {
			file_put_contents($downloadPath, file_get_contents($downloadUrl));
		}

		`unzip -o "$downloadPath" -d "$downloadDir"`;

		if (null === $version) $version = LocalInstallUtils::getPluginVersionFromPluginDirectory($downloadDir . '/' . $plugin);// get_plugin_version_from_directory($downloadDir . '/' . $plugin);
		if (empty($version)) throw new Exception('Unable to determine plugin version');

		// @deprecated ??? if (substr($downloadPath, 0, strlen(REPO_PLUGIN_DIR)) != REPO_PLUGIN_DIR) die('Something is terribly wrong.  Cannot delete non download directory');

		$unzipped_plugin_path = substr($downloadPath, 0, -4);
		$unzipped_plugin_path = str_replace('.' . $version, '', $unzipped_plugin_path);

		if (!file_exists($unzipped_plugin_path)) throw new \RuntimeException('Could not find plugin path: ' . $unzipped_plugin_path . PHP_EOL);
		// copy_files
		$destination_path = $repo->getPluginPath($plugin, $version);

		if (!file_exists($destination_path)) mkdir($destination_path, 0755, true);

		FileUtils::recursivelyCopyFiles($unzipped_plugin_path, $destination_path);

		`rm -rf "$unzipped_plugin_path"`;
		`rm -rf "$downloadPath"`;
	}

	protected function getQuestionHelper(): QuestionHelper
	{
		return $this->getHelper('question');
	}
}