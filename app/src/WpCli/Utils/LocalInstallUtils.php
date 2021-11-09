<?php

namespace App\WpCli\Utils;

use App\WpCli\FileUtils;
use App\WpCli\PluginManagement\LocalPlugin;
use App\WpCli\PluginManagement\Repository;

class LocalInstallUtils
{
	const LOCAL_PLUGIN_DIR = '/wp-content/plugins';

	private static function normalizeRootPath(string $pathToRoot)
	{
		return rtrim($pathToRoot, '/\\');
	}
	/**
	 * Ensure that we are in the directory of a WordPress installation
	 */
	public static function isWordPressInstall(string $pathToWpRoot)
	{
		return file_exists(self::normalizeRootPath($pathToWpRoot) . '/wp-config.php');
	}

	/**
	 * @param string The name/key used to identify a plugin
	 * @return string|null String if version is found, otherwise null
	 */
	public static function getPluginVersion(string $pathToWpRoot, string $plugin)
	{
		$path = self::normalizeRootPath($pathToWpRoot) . self::getLocalPluginPath($plugin);

		$version = self::getPluginVersionFromPluginDirectory($path);

		return $version;
	}

	public static function getPluginVersionFromPluginDirectory(string $path): ?string
	{
		$files = glob($path . '/*.php');
//		echo __FUNCTION__ . '(' . $path . ')' . PHP_EOL;
		foreach ($files as $file) {
//			echo '- Searching ' . $file . '...' . PHP_EOL;
			$contents = file_get_contents($file);
			if (preg_match('#/\*.*Plugin Name:.*?\*/#ims', $contents, $matches)) {
				list($match) = $matches;
				if (preg_match('/Version: *([a-z0-9\.]+)/', $match, $ver_match)) {
					list($_, $ver) = $ver_match;
					return $ver;
				}
			}
		}

		return null;
	}

	/**
	 * Show the list of locally installed plugins
	 * @return LocalPlugin[]
	 */
	public static function getPlugins(Repository $repo, string $pathToWpRoot)
	{
		$dirs = glob(self::normalizeRootPath($pathToWpRoot) . self::LOCAL_PLUGIN_DIR . '/*', GLOB_ONLYDIR);

		$plugins = [];

		foreach ($dirs as $dir) {
			$plugin_name = basename($dir);

			$version = self::getPluginVersionFromPluginDirectory($dir);

			$plugins[] = new LocalPlugin(
				$plugin_name,
				$version ?: '',
				$version === null ? false : $repo->hasPluginVersion($plugin_name, $version),
				$repo->getPluginVersions($plugin_name)
			);
		}

		return $plugins;
	}

	/**
	 * Check whether a plugin (and optional version) are installed locally
	 * @param string $plugin
	 * @param string|null $version
	 *
	 * @return bool
	 */
	public static function hasPlugin(string $pathToWpRoot, string $plugin, string $version = null)
	{
		$version_installed = self::getPluginVersion($pathToWpRoot, $plugin);

		if (null === $version_installed) return false; // not installed at all
		else if (null !== $version && $version != $version_installed) return false; // requested version is not installed

		return true;
	}

	/**
	 * Install a plugin version from the repo into the local install
	 *
	 * @param string $plugin
	 * @param string $version
	 * @return string
	 * @throws Exception
	 */
	public static function installPlugin(Repository $repo, string $pathToWpRoot, string $plugin, string $version)
	{
		if (empty($plugin)) throw new Exception('Cannot copy plugin from repo when plugin is not specified');
		if (empty($version)) throw new Exception('Cannot copy plugin from repo when version is not specified');

		if (!$repo->hasPluginVersion($plugin, $version)) throw new Exception(sprintf('The requested plugin version cannot be found in the report %s v%s', $plugin, $version));
		else if (self::hasPlugin($pathToWpRoot, $plugin, $version)) return 'Already installed'; // No work to be done, it is already installed

		$local_path = self::normalizeRootPath($pathToWpRoot) . self::getLocalPluginPath($plugin);

		if (file_exists($local_path)) FileUtils::recursivelyRemoveDirectory($local_path);    // If directory exists, remove all files before installing
		else mkdir($local_path);

		$repo_path = $repo->getPluginPath($plugin, $version);

		if (!file_exists($repo_path)) mkdir($repo_path, 0755, true);
		throw new \Exception('NOT IMPLEMENTED: About to copy ' . $repo_path . ' to ' . $local_path);
		FileUtils::recursivelyCopyFiles($repo_path, $local_path);
	}

	public static function getLocalPluginPath(string $plugin)
	{
		return self::LOCAL_PLUGIN_DIR . '/' . $plugin;
	}
}