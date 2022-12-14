<?php

namespace Elgg\Menu;

use Elgg\UnitTestCase;

/**
 * @group Menus
 */
class TitleMenuTest extends UnitTestCase {

	public function up() {
		_elgg_services()->events->backup();

		_elgg_services()->events->registerHandler('register', 'menu:title', 'Elgg\Menus\Title::registerAvatarEdit');
	}
	
	public function down() {
		_elgg_services()->events->restore();
	}

	public function testTitleMenuWithoutUser() {

		$menu = _elgg_services()->menus->getUnpreparedMenu('title', [
			'entity' => 'foo',
		]);

		$items = $menu->getItems();

		$this->assertEmpty($items->all());
	}

	public function testTitleMenuViewedByGuest() {

		$menu = _elgg_services()->menus->getUnpreparedMenu('title', [
			'entity' => $this->createUser(),
		]);

		$items = $menu->getItems();

		$this->assertEmpty($items->all());
	}

	public function testTitleMenuViewedByUser() {

		$viewer = $this->createUser();
		_elgg_services()->session->setLoggedInUser($viewer);

		$menu = _elgg_services()->menus->getUnpreparedMenu('title', [
			'entity' => $this->createUser(),
		]);

		$items = $menu->getItems();

		$this->assertFalse($items->has('avatar:edit'));
	}

	public function testTitleMenuViewedBySelf() {

		$viewer = $this->createUser();
		_elgg_services()->session->setLoggedInUser($viewer);

		$menu = _elgg_services()->menus->getUnpreparedMenu('title', [
			'entity' => $viewer,
		]);

		$items = $menu->getItems();

		$this->assertTrue($items->has('avatar:edit'));
	}

	public function testTitleMenuViewedByAdmin() {

		$viewer = $this->createUser([
			'admin' => 'yes',
		]);
		_elgg_services()->session->setLoggedInUser($viewer);

		$menu = _elgg_services()->menus->getUnpreparedMenu('title', [
			'entity' => $this->createUser(),
		]);

		$items = $menu->getItems();

		$this->assertTrue($items->has('avatar:edit'));
	}
}
