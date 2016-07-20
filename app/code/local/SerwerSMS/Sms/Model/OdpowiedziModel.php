<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Model_OdpowiedziModel extends Mage_Core_Model_Abstract{
    
    public function _construct(){
        parent::_construct();
        $this->_init('serwersms_model/odpowiedziModel');
    }
}

?>