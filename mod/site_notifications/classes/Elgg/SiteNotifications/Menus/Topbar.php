<?php

namespace Elgg\SiteNotifications\Menus;

/**
 * Event callbacks for menus
 *
 * @since 4.0
 * @internal
 */
class Topbar {
	
	/**
	 * Adds menu item
	 *
	 * @param \Elgg\Event $event 'register', 'menu:topbar'
	 *
	 * @return void|\Elgg\Menu\MenuItems
	 */
	public static function register(\Elgg\Event $event) {
		$user = elgg_get_logged_in_user_entity();
		if (empty($user)) {
			return;
		}
		
		$count = elgg_count_entities([
			'type' => 'object',
			'subtype' => 'site_notification',
			'owner_guid' => $user->guid,
			'metadata_name_value_pairs' => [
				'read' => false,
			],
		]);
		
		$return = $event->getValue();
		$return[] = \ElggMenuItem::factory([
			'name' => 'site_notifications',
			'href' => elgg_generate_url('collection:object:site_notification:owner', [
				'username' => $user->username
			]),
			'text' => elgg_echo('site_notifications:topbar'),
			'icon' => 'bell',
			'badge' => $count ?: null,
			'priority' => 100,
		]);
	
		return $return;
	}
}
