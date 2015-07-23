<?php

class GH_Statements_Model_Resource_Track extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/track', 'id');
    }


	public function appendTracks($data) {
		$writeConnection = $this->_getWriteAdapter();
		$writeConnection->insertMultiple($this->getTable('ghstatements/track'), $data);
	}

}