<?php

class Zolago_Banner_Model_Resource_Banner extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagobanner/banner', "banner_id");
    }
}

