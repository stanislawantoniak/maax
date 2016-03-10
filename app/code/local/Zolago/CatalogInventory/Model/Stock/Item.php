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
        $stockStatusModel->assignProduct($product, $this->getStockId(), $this->getStockStatusInPos());

        if ($this->getAlwaysInStock()) {
            $product->setIsSalable(true);
        }
        return $this;
    }

	/**
	 * Retrieve stock_status
	 *
	 * @return int
	 * @throws Mage_Core_Exception
	 */
    public function getStockStatusInPos()
    {
        if ($this->isFlagUsePos()) {
			if (!is_null($this->posStockStatus)) {
				return $this->posStockStatus;
			}
            $productId = $this->getProductId();
			$websiteId = Mage::app()->getWebsite()->getId(); //todo czy id brac z produkt ?

            $resource = $this->getResource();
            $posStockTable = $resource->getTable("zolagopos/stock");
            $select = $resource->getReadConnection()->select()
                ->from(array("pos_stock" => $posStockTable),
                    array(
                        'product_id',
                        "qty" => new Zend_Db_Expr("SUM(pos_stock.qty)")
                    )
                )
                ->joinLeft(
                    array('pos' => $resource->getTable("zolagopos/pos")),
                    "pos.pos_id = pos_stock.pos_id",
                    array()
                )
                ->joinLeft(
                    array('pos_website' => $resource->getTable("zolagopos/pos_vendor_website")),
                    "pos_website.pos_id = pos.pos_id",
                    array()
                )
                ->where("pos_stock.product_id=?", $productId)
                ->where('pos_website.website_id=?', $websiteId)
                ->where("pos.is_active = ?", Zolago_Pos_Model_Pos::STATUS_ACTIVE)
                ->group("pos_stock.product_id");

            $result = $resource->getReadConnection()->fetchRow($select);

			if (empty($result)) {
				// check for children
				/** @var Zolago_CatalogInventory_Model_Resource_Stock_Status $model */
				$model = Mage::getResourceModel("zolago_cataloginventory/stock_status");
				$result = $model->getProductStatus($productId, $websiteId, Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID, true);
				$result = current($result);
			}

			$this->posQty			= isset($result["qty"]) ? $result["qty"] : 0;
            $this->posStockStatus	= (int)($this->posQty > 0);
			$this->posIsInStock		= $this->posStockStatus;
			return $this->posStockStatus;
        }
        return $this->getStockStatus();
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
		if ($key == 'stock_status') {
			return $this->getStockStatus();
		}
		return parent::getData($key, $index);
	}

	public function getQty() {
		if (!$this->isFlagUsePos()) {
			return parent::getQty();
		}
		$this->getStockStatusInPos(); // get and set posQty
		return $this->posQty;
	}

	public function getIsInStock() {
		if (!$this->isFlagUsePos()) {
			return parent::getIsInStock();
		}
		$this->getStockStatusInPos(); // get and set posIsInStock
		return $this->posIsInStock;
	}

	public function getStockStatus() {
		if (!$this->isFlagUsePos()) {
			return parent::getStockStatus();
//			return $this->_getData('stock_status');
		}
		if (is_null($this->posStockStatus)) {
			$stockStatus = $this->getStockStatusInPos(); // get and set posQty, posIsInStock ans posStockStatus
			if ($this->getProduct()->getId()) {
				$this->getProduct()->setIsSalable($stockStatus);
			}
		}
		return $this->posQty;
	}
}