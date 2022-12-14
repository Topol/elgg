<?php

namespace Elgg\Menus;

use Elgg\Menu\MenuItems;

/**
 * Register menu items to the annotation menu
 *
 * @since 4.0
 * @internal
 */
class Annotation {

	/**
	 * Register the generic delete annotation menu item
	 *
	 * @param \Elgg\Event $event 'register', 'menu:annotation'
	 *
	 * @return void|MenuItems
	 */
	public static function registerDelete(\Elgg\Event $event) {
		$annotation = $event->getParam('annotation');
		if (!$annotation instanceof \ElggAnnotation || !$annotation->canEdit()) {
			return;
		}
		
		/* @var $result MenuItems */
		$return = $event->getValue();
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'delete',
			'icon' => 'delete',
			'text' => elgg_echo('delete'),
			'href' => elgg_generate_action_url('annotation/delete', [
				'id' => $annotation->id,
			]),
			'confirm' => elgg_echo('deleteconfirm'),
		]);
		
		return $return;
	}
}
