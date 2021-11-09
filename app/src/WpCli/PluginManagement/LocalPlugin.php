<?php

namespace App\WpCli\PluginManagement;

class LocalPlugin {
	private $name;
	private $version;
	private $inRepo = false;
	private $availableVersions = [];
	private $upgradeAvailable = false;

	/**
	 * LocalPlugin constructor.
	 * @param $name
	 * @param bool $inRepo
	 * @param array $availableVersions
	 * @param bool $upgradeAvailable
	 */
	public function __construct(string $name, string $version, bool $inRepo, array $availableVersions=[])
	{
		$this->name = $name;
		$this->version = $version;
		$this->inRepo = $inRepo;
		$this->availableVersions = $availableVersions;
	}

	/**
	 * @return mixed
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	/**
	 * @return bool
	 */
	public function isInRepo(): bool
	{
		return $this->inRepo;
	}

	/**
	 * @return array
	 */
	public function getAvailableVersions(): array
	{
		return $this->availableVersions;
	}

	/**
	 * @return bool
	 */
	public function isUpgradeAvailable(): bool
	{
		foreach ($this->getAvailableVersions() as $ver) {
			if (version_compare($ver, $this->getVersion(), '>')) return true;
		}

		return false;
	}
}