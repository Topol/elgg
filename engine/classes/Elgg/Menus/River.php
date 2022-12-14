<?php

namespace Elgg\Menus;

use Elgg\Menu\MenuItems;

/**
 * Register menu items to the river menu
 *
 * @since 4.0
 * @internal
 */
class River {

	/**
	 * Add the delete to river actions menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:river'
	 *
	 * @return void|MenuItems
	 */
	public static function registerDelete(\Elgg\Event $event) {
		
		$item = $event->getParam('item');
		if (!$item instanceof \ElggRiverItem || !$item->canDelete()) {
			return;
		}
		
		/* @Var $return MenuItems */
		$return = $event->getValue();
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'delete',
			'icon' => 'delete',
			'text' => elgg_echo('river:delete'),
			'href' => elgg_generate_action_url('river/delete', [
				'id' => $item->id,
			]),
			'confirm' => elgg_echo('deleteconfirm'),
			'priority' => 999,
		]);
		
		return $return;
	}
}
