<?php
/**
 * @category SolrBridge
 * @package SolrBridge_Solrsearch
 * @author	Hau Danh
 * @copyright	Copyright (c) 2011-2014 Solr Bridge (http://www.solrbridge.com)
 *
 */
class SolrBridge_Solrsearch_ByController extends Mage_Core_Controller_Front_Action
{
	public function categoryAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

	public function brandAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
}