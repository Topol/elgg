<?php

namespace Elgg\Cache;

/**
 * @group Cache
 */
class RuntimeCacheTest extends BaseCacheTestCase {

	function createCache(string $namespace = 'runtime_test') {
		return new CompositeCache($namespace, _elgg_services()->config, ELGG_CACHE_RUNTIME);
	}
}
