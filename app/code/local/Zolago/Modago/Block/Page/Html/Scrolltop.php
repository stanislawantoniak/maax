<?php
class Zolago_Modago_Block_Page_Html_Scrolltop extends Mage_Core_Block_Template {
	const SCROLLTOP_CONFIG_PATH_ACTIVE = 'design/scrolltop/active';
	const SCROLLTOP_CONFIG_PATH_POSITION_RIGHT = 'design/scrolltop/position_right';
	const SCROLLTOP_CONFIG_PATH_POSITION_BOTTTOM = 'design/scrolltop/position_bottom';
	const SCROLLTOP_CONFIG_PATH_PERCENT_APPEARS = 'design/scrolltop/percent_appears';
	const SCROLLTOP_CONFIG_PATH_SHOW_ON_SCROLL = 'design/scrolltop/show_on_scroll';
	const SCROLLTOP_CONFIG_PATH_HIDE_AFTER = 'design/scrolltop/hide_after';

	public function isActive() {
		$isOpera = $this->isOperaMobile();
		return !$isOpera ? Mage::getStoreConfig(self::SCROLLTOP_CONFIG_PATH_ACTIVE) : false;
	}

	protected function isOperaMobile() {
		return preg_match('/opera m(ob|in)i/i',$_SERVER['HTTP_USER_AGENT']);
	}

	public function getPositionRight() {
		return Mage::getStoreConfig(self::SCROLLTOP_CONFIG_PATH_POSITION_RIGHT);
	}

	public function getPositionBottom() {
		return Mage::getStoreConfig(self::SCROLLTOP_CONFIG_PATH_POSITION_BOTTTOM);
	}

	public function getPercentAppears() {
		return Mage::getStoreConfig(self::SCROLLTOP_CONFIG_PATH_PERCENT_APPEARS);
	}

	public function showOnScroll() {
		return Mage::getStoreConfig(self::SCROLLTOP_CONFIG_PATH_SHOW_ON_SCROLL);
	}

	public function getHideAfter() {
		return Mage::getStoreConfig(self::SCROLLTOP_CONFIG_PATH_HIDE_AFTER);
	}
}