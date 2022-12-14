<?php

namespace Elgg\Page;

/**
 * Add manifest link to page head
 *
 * @since 4.0
 */
class AddManifestLinkHandler {
	
	/**
	 * Adds the manifest.json to head links
	 *
	 * @param \Elgg\Event $event 'head', 'page'
	 *
	 * @return array
	 */
	public function __invoke(\Elgg\Event $event) {
		$result = $event->getValue();
		$result['links']['manifest'] = [
			'rel' => 'manifest',
			'href' => elgg_get_simplecache_url('resources/manifest.json'),
		];
	
		return $result;
	}
}
