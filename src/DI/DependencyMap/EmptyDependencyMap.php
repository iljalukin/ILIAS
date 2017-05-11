<?php

namespace ILIAS\DI\DependencyMap;

use ILIAS\DI\Container;
use ILIAS\DI\Exceptions\NoSuchServiceException;

/**
 * Class BaseDependencyMap
 *
 * @package ILIAS\DI
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class EmptyDependencyMap implements DependencyMap {

	/**
	 * @var callable[]
	 */
	protected $maps = [];

	/**
	 * @inheritdoc
	 */
	public function getDependencyWith(Container $DIC, string $fullyQualifiedDomainName, string $for, callable $map) {
		$result = $map($DIC, $fullyQualifiedDomainName, $for);
		if($result) {
			return $result;
		} else {
			return $this->getDependency($DIC, $fullyQualifiedDomainName, $for);
		}
	}

	/**
	 * Returns a new dependency map with the given mapping. The newer mapping always comes first!
	 *
	 * @param callable $map (Container $DIC, string $fullyQualifiedDomainName, string $for) => mixed|null
	 *
	 * @return static
	 */
	public function with(callable $map) {
		$dependencyMap = new static();
		$dependencyMap->maps = array_merge([$map], $this->maps);
		return $dependencyMap;
	}

	/**
	 * @inheritdoc
	 */
	public function getDependency(Container $DIC, string $fullyQualifiedDomainName, string $for) {
		foreach ($this->maps as $map) {
			$result = $map($DIC, $fullyQualifiedDomainName, $for);
			if($result) {
				return $result;
			}
		}

		throw new NoSuchServiceException("The requested service ".$fullyQualifiedDomainName." could not be resolved.");
	}
}