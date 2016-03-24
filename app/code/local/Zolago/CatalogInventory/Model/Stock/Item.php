<?php

/**
 * Class Zolago_CatalogInventory_Model_Stock_Item
 */
class Zolago_CatalogInventory_Model_Stock_Item extends Unirgy_Dropship_Model_Stock_Item {

	/**
	 * Data from POS for override values when usePos flag is ON
	 */
	protected $posStockStatus = null;
	protected $posIsInStock = null;
	protected $posQty = null;
	protected $_helper = null;

    /**
     * Adding stock data to product
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function assignProduct(Mage_Catalog_Model_Product $product)
    {

        if (!$this->getId() || !$this->getProductId()) {
            $this->_getResource()->loadByProductId($this, $product->getId());
            $this->setOrigData();
        }

        $this->setProduct($product);
        $product->setStockItem($this);
		$product->setIsInStock($this->getIsInStock());
		/** @var Mage_CatalogInventory_Model_Stock_Status $stockStatusModel */
		$stockStatusModel = Mage::getSingleton('cataloginventory/stock_status');
        $stockStatusModel->assignProduct($product, $this->getStockId());

        if ($this->getAlwaysInStock()) {
            $product->setIsSalable(true);
        }
        return $this;
    }
    
    /**
     * set helper
     */
     public function setHelper($helper) {
         $this->_helper = $helper;
     }
    /**
     * get helper
     * @param 
     * @return 
     */
     protected function _getHelper() {
         if (!$this->_helper) {
             $this->_helper = Mage::helper('zolagocataloginventory');
         }
         return $this->_helper;
     }
    /**
     * get actual website id
     *
     * @return int
     */
    protected function _getWebsiteId() {	
        return $this->_getHelper()->getWebsiteId();
    }
	/**
	 * Wrapper for flag
	 *
	 * @return bool
	 */
	public function isFlagUsePos() {
		// todo make set registry flag in some front event
		return 1;//$this->hasFlagUsePos();
	}
	
    /**
     * override setData (is_in_stock by website)
     */
    public function setData($key,$value = null) {
        if ($key == 'is_in_stock') {
            $this->setIsInStock($value);
        }
        return parent::setData($key,$value);
    }
    /**
     * override addData (is in stock by website)
     */

	public function addData(array $arr) {
	    if (isset($arr['is_in_stock'])) {
	        $this->setIsInStock($arr['is_in_stock']);
	    }
	    return parent::addData($arr);
	}
	
	
    /**
     * set is_in_stock by website
     * @param int $value
     */

	public function setIsInStock($value) {
	    $instock = $this->getInStock();
	    $websiteId = $this->_getWebsiteId();
	    $instock[$websiteId] = $value;
	    $this->setInStock($instock);
	}
	/**
	 * Override for compatibility
	 *
	 * @param string $key
	 * @param null $index
	 * @return bool|float|int|mixed|null
	 */
	public function getData($key = '', $index = null) {
		if ($key == 'qty') {
			return $this->getQty();
		}
		if ($key == 'is_in_stock') {
			return $this->getIsInStock();
		}
		return parent::getData($key, $index);
	}

	public function getQty() {
        $website = $this->_getWebsiteId();	    
		if (!$this->isFlagUsePos()) {
			return parent::getQty();
		}
        $qty = $this->getData('website_qty');
        return isset($qty[$website])? $qty[$website]:0;
	}

	public function getIsInStock() {
		if (!$this->isFlagUsePos()) {
			return parent::getIsInStock();
		}
        if (!$this->getManageStock()) {
            return true;
        }

        $website = $this->_getWebsiteId();	    
		$instock = $this->getInStock();
		return empty($instock[$website])? false:true;		
	}	
	protected function _afterSave() {
	    $model = Mage::getResourceModel('zolagocataloginventory/stock_website');
	    $model->saveStockWebsite($this);
	    return parent::_afterSave();
	}
}