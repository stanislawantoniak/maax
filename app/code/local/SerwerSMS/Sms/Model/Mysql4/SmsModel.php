<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Model_Mysql4_SmsModel extends Mage_Core_Model_Mysql4_Abstract{
    
    public function _construct(){
        $this->_init('serwersms_model/SmsModel','id');
    }
}

?>