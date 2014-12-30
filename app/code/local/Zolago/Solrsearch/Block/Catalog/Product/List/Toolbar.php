<?php
class Zolago_Solrsearch_Block_Catalog_Product_List_Toolbar extends Mage_Core_Block_Template {

	public function _construct() {
		$this->setTemplate('zolagosolrsearch/catalog/product/list/toolbar.phtml');
		parent::_construct();
	}
	/**
	 * @return int
	 */
	public function getNumFound() {
		$num = $this->getCollection()->getSolrData("response", "numFound");
		if(is_numeric($num)){
			return $num;
		}
		return 0;
	}
	
	/**
	 * @return array
	 */
	public function getSortOptions() {
		return $this->getListModel()->getSortOptions();
	}
	
	public function getCurrentOrder() {
		return $this->getListModel()->getCurrentOrder();
	}
	
	public function getCurrentDir() {
		return $this->getListModel()->getCurrentDir();
	}
	
	/**
	 * @param type $option
	 * @return array
	 */
	public function getSortUrl($option) {
		return $this->getPagerUrl(array("sort"=>$option['value'], "dir"=>$option['dir']));
	}
	
	/**
	 * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
	 */
	public function getCollection() {
		return $this->getListModel()->getCollection();
	}
	
	/**
	 * @return Zolago_Solrsearch_Model_Catalog_Product_List
	 */
	public function getListModel() {
		return Mage::getSingleton('zolagosolrsearch/catalog_product_list');
	}
	
	public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        // overwrite q
        $q = $this->getRequest()->getParam('q');
        if (!empty($q)) {
            $solr = $this->getListModel()->getSolrData();
            if (!empty($solr['responseHeader']['params']['q'])) {
                $params['q'] = $solr['responseHeader']['params']['q'];
                $urlParams['q'] = $solr['responseHeader']['params']['q'];
            }
        }
        $urlParams['_query']    = Mage::helper('zolagosolrsearch')->processFinalParams($params);
        $url = $this->getUrl('*/*/*', $urlParams);
        return $url;
    }
}
