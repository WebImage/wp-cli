<?php

namespace App\WpCli\PluginManagement;

use WebImage\Application\AbstractCommand;

class Repository
{
	private $path;

	/**
	 * Repository constructor.
	 * @param $path
	 */
	public function __construct(string $path)
	{
		$this->setPath($path);
	}

	/**
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	private function setPath(string $path)
	{
		if (!file_exists($path)) throw new \RuntimeException('Plugin repository directory not found: ' . $path);

		$this->path = rtrim($path, '/\\');
	}

	/**
	 * @return Plugin[]
	 */
	public function getPlugins()
	{
		$plugins = [];

		$plugin_dirs = glob($this->getPath() . '/*', GLOB_ONLYDIR);
		sort($plugin_dirs);

		foreach($plugin_dirs as $plugin_dir) {
			$name = basename($plugin_dir);
			if (substr($name, 0, 1) == '_') continue; // Hidden directories

			$plugin = new Plugin($name);
			$plugins[] = $plugin;

			$plugin->addVersions($this->getPluginVersions($name));
		}

		return $plugins;
	}

	/**
	 * @param string $pluginName
	 * @return string[]
	 */
	public function getPluginVersions(string $pluginName): array
	{
		$path = $this->getPluginPath($pluginName);
		$versions = [];

		$paths = glob($path . '/*', GLOB_ONLYDIR);

		foreach($paths as $path) {
			$versions[] = basename($path);
		}

		return $versions;
	}

	public function hasPluginVersion(string $plugin, string $version)
	{
		return file_exists($this->getPluginPath($plugin, $version));
	}

	/**
	 * Get the path to a plugin or a version within a plugin
	 * @param $plugin
	 * @param null $version
	 * @return string
	 */
	public function  getPluginPath($plugin, $version=null): string
	{
		if (null === $version) return sprintf('%s/%s', $this->path, $plugin);

		return sprintf('%s/%s/%s', $this->path, $plugin, $version);
	}

	/**
	 * Whether the plugin is marked as skipped (i.e. a .skip file exists at the root of the plugin repo folder).  Typically this would be for private plugins that cannot be automatically downloaded
	 * @param string $plugin
	 * @return bool
	 */
	public function shouldSkipPluginDownload(string $plugin)
	{
		return file_exists($this->getPluginPath($plugin) . '/.skip');
	}
}