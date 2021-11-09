<?php

namespace App\WpCli\PluginManagement;

class Plugin
{
	private $name;
	private $versions = [];

	/**
	 * PluginInfo constructor.
	 * @param $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	public function addVersion($version)
	{
		$this->versions[] = $version;
		usort($this->versions, 'version_compare');
	}

	public function addVersions(array $versions)
	{
		foreach($versions as $version) {
			$this->addVersion($version);
		}
	}

	/**
	 * @return string[]
	 */
	public function getVersions()
	{
		return $this->versions;
	}
}