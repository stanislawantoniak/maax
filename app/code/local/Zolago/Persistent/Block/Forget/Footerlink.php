<?php
class Zolago_Persistent_Block_Forget_Footerlink extends Zolago_Persistent_Block_Forget_Abstract
{
	public function _construct() {
		parent::_construct();
        $this->setTemplate('zolagopersistent/forget/footerlink.phtml');
	}
}
