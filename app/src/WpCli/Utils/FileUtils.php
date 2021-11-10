<?php

namespace App\WpCli\Utils;

class FileUtils
{
	/**
	 * Recursively copy file structure
	 * @param string $fromPath
	 * @param string $toPath
	 * @throws \Exception
	 */
	public static function recursivelyCopyFiles(string $fromPath, string $toPath)
	{
		if (!file_exists($toPath)) {
			throw new \Exception(sprintf('The target path %s does not exist', $toPath));
		}

		$files = glob($fromPath . '/*');

		foreach($files as $file) {
			$filename = basename($file);
			$target_path = $toPath . '/' . $filename;
			if (filetype($file) == 'dir') {
				if (!file_exists($target_path)) mkdir($target_path);
				self::recursivelyCopyFiles($file, $target_path, false);
			} else if (filetype($file) == 'file') {
				copy($file, $target_path);
			}
		}
	}

	/**
	 * @param string $local_path An absolute local path
	 * @param string $relative_dir The path relative to the $local_path
	 */
	public static function recursivelyRemoveDirectory(string $local_path) {
		$paths = glob($local_path . '/*');

		foreach($paths as $path) {
			$basename = basename($path);

			if (filetype($path) == 'dir') {
				self::clearDir($local_path . '/' . $basename);
				rmdir($path);
			} else if (filetype($path) == 'file') {
				unlink($path);
			}
		}
	}
}
