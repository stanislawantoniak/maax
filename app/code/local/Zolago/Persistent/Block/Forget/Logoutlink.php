<?php
class Zolago_Persistent_Block_Forget_Logoutlink extends Zolago_Persistent_Block_Forget_Abstract
{
	public function _construct() {
		parent::_construct();
        $this->setTemplate('zolagopersistent/forget/logoutlink.phtml');
	}
}
