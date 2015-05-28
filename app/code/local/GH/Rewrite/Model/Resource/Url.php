<?php

/**
 * url rewrite resource
 */
class GH_Rewrite_Model_Resource_Url extends Mage_Core_Model_Resource_Db_Abstract
{

	protected function _construct()
	{
		$this->_init('ghrewrite/url', "url_id");
	}

	public function appendRewrites($data)
	{
		$writeConnection = $this->_getWriteAdapter();
		$writeConnection->insertMultiple($this->getTable('ghrewrite/url'), $data);
	}
}