<?php

namespace Elgg\Di;

use Laminas\Mail\Transport\InMemory;
use phpDocumentor\Reflection\DocBlock\Tag;
use Elgg\IntegrationTestCase;
use Elgg\Project\Paths;

class PublicContainerIntegrationTest extends IntegrationTestCase {

	/**
	 * @dataProvider servicesListProvider
	 */
	public function testPropertyType($name, $type) {
		$service = elgg()->{$name};

		// support $type like "Foo\Bar|Baz|null"
		$passed = false;
		foreach (explode('|', $type) as $test_type) {
			if ($test_type === 'null') {
				if ($service === null) {
					$passed = true;
				}
			} elseif ($service instanceof $test_type) {
				$passed = true;
			}
		}
		$this->assertTrue($passed, "{$name} did not match type {$type}");
	}

	public function testListProvider() {
		$services = elgg();

		$list = [];
		foreach (self::servicesListProvider() as $item) {
			$list[$item[0]] = $item[1];
		}

		$errors = [];
		
		$services = include Paths::elgg() . 'engine/public_services.php';
		$services = array_keys($services);
		$this->assertNotEmpty($services);
		
		foreach ($services as $name) {
			if (isset($list[$name])) {
				continue;
			}
			
			if (class_exists($name) || interface_exists($name)) {
				// we only check alias names not full classes
				continue;
			}

			$errors[] = "$name is not present in data provider";
		}

		if ($errors) {
			$this->fail(implode(PHP_EOL, $errors));
		}
	}

	public static function servicesListProvider() {
		$class = new \ReflectionClass(PublicContainer::class);
		$factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
		$phpdoc = $factory->create($class);
		
		$readonly_props = $phpdoc->getTagsByName('property-read');
		$sets = [];
		/* @var Tag[] $readonly_props */
		foreach ($readonly_props as $prop) {
			$sets[] = [
				$prop->getVariableName(),
				$prop->getType(),
			];
		}

		return $sets;
	}
}
