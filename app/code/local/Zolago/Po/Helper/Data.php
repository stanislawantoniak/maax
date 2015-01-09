<?php
class Zolago_Po_Helper_Data extends Unirgy_DropshipPo_Helper_Data
{
	const DEFAULT_PO_IMAGE_WIDTH = 53;
	const DEFAULT_PO_IMAGE_HEIGHT = 69;
	
	protected $_condJoined = false;	
	
	/**
	 * @param Zolago_Po_Model_Po_Item $item
	 * @return Mage_Catalog_Helper_Image
	 */
	public function getItemThumbnail(Zolago_Po_Model_Po_Item $item) { 
        return Mage::helper('catalog/image')->init($item->getProduct(), 'thumbnail');
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 * @return array
	 */
	public function getPoImagesAsAttachments(Zolago_Po_Model_Po $po, $options=array()) {
		$width = $this->getPoImageWidth($po);
		$height = $this->getPoImageHeight($po);
		$store = $po->getStore();
		$attachments = array();
		
		foreach($po->getItemsCollection() as $item){
			if(!$item->getParentItemId()){
				$imageUrl = (string)Mage::helper("catalog/image")->
					init($item->getProduct(), "thumbnail")->
					resize($width, $height);
				$attachments[] = array(
					"filename"		=> Mage::helper("zolagocommon")->getRelativePath($imageUrl, $store),
					"id"			=> basename($item->getId().".jpg"),
					"disposition"	=> "inline"
				);
			}
		}
		
		return $attachments;
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 * @return int
	 */
	public function getPoImageWidth(Zolago_Po_Model_Po $po) {
		return self::DEFAULT_PO_IMAGE_WIDTH;
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 * @return int
	 */
	public function getPoImageHeight(Zolago_Po_Model_Po $po) {
		return self::DEFAULT_PO_IMAGE_HEIGHT;
	}
	
	/**
	 * Clear not used addresses (trashes)
	 * @param Zolago_Po_Model_Po $po
	 * @param type $type
	 * @param type $exclude
	 */
	public function clearAddresses(Zolago_Po_Model_Po $po, $type, $exclude=array()) {
		
	    // Add this shippign id
		// $exclude[] = $po->getShippingAddressId();
	    $addressCollection = Mage::getResourceModel("sales/order_address_collection");
		/* @var $addressCollection Mage_Sales_Model_Resource_Order_Address_Collection */
		$addressCollection->addFieldToFilter("parent_id", $po->getOrder()->getId());
		$addressCollection->addFieldToFilter("address_type", $type);
		
		if($exclude){
			$addressCollection->addFieldToFilter("entity_id", array("nin"=>$exclude));
		}
		
		$select = $addressCollection->getSelect();
		
		if($type==$po::TYPE_POSHIPPING || $type==Zolago_Rma_Model_Rma::TYPE_RMASHIPPING){

			$subSelect = $select->getAdapter()->select();
			$subSelect->from( 
					array("shipment"=>$po->getResource()->getTable("sales/shipment")),
					array(new Zend_Db_Expr("COUNT(shipment.entity_id)"))
			);
			$subSelect->where("shipment.shipping_address_id=main_table.entity_id");

			$select->where("? < 1", $subSelect);
		}
		
		// Skip used addresses

		$subSelect = $select->getAdapter()->select();
		$subSelect->from( 
				array("self"=>$po->getResource()->getMainTable()),
				array(new Zend_Db_Expr("COUNT(self.entity_id)"))
		);
		$subSelect->where("self.shipping_address_id=main_table.entity_id OR self.billing_address_id=main_table.entity_id");

		$select->where("? < 1", $subSelect);
		
		// Skip RMA used addresse
		$subSelect = $select->getAdapter()->select();
		$subSelect->from( 
				array("rma"=>$po->getResource()->getTable("urma/rma")),
				array(new Zend_Db_Expr("COUNT(rma.entity_id)"))
		);
		$subSelect->where("rma.shipping_address_id=main_table.entity_id OR rma.billing_address_id=main_table.entity_id");

		$select->where("? < 1", $subSelect);
		
		foreach($addressCollection as $toDelete){
			$toDelete->delete();
		}
   
	}
	
	public function sendNewPoNotificationEmail($po, $comment=''){
		$vendor = $po->getVendor();
		/* @var $po Zolago_Po_Model_Po */
		$order = $po->getOrder();
        $store = $order->getStore();
		$pos = $po->getPos();
		
		$emailField = $store->getConfig('udropship/vendor/vendor_notification_field');
		
		if(!$emailField){
			$emailField = "email";
		}
		
		$oldEmail = $newEmail = $vendor->getData($emailField);
		
		if($pos && $pos->getId()){
			$newEmail = $pos->getEmail();
		}
		
		// Replace vendor email to pos email & send mail & restore origin
		$vendor->setData($emailField, $newEmail);	
		$vendor->setData("po", $po);
		$return = parent::sendNewPoNotificationEmail($po, $comment);
		$vendor->setData($emailField, $oldEmail);
		$vendor->setData("po", null);
		
		
		// Porocess queue
		Mage::helper('udropship')->processQueue();
		
		return $return; 
	}
	
	/**
	 * @param type $shipment
	 * @param type $save
	 * @return type
	 */
    public function cancelShipment($shipment, $save){
		Mage::dispatchEvent("zolagopo_shipment_cancel_before", array("shipment"=>$shipment));
		$return = parent::cancelShipment($shipment, $save);
		Mage::dispatchEvent("zolagopo_shipment_cancel_after", array("shipment"=>$shipment));
		return $return;
	}
	/**
	 * @param Unirgy_DropshipPo_Model_Mysql4_Po_Collection|array $collection
	 * @param Unirgy_Dropship_Model_Vendor | int $vendor
	 * @return boolean
	 * @throws Mage_Core_Exception
	 */
	public function createAggregated($collection, $vendor) {
		
		if($vendor instanceof Unirgy_Dropship_Model_Vendor){
			$vendor = $vendor->getId();
		}
		
		if(is_array($collection)){
			$collection = Mage::getResourceModel('udpo/po_collection');
			/* @var $collection Unirgy_DropshipPo_Model_Mysql4_Po_Collection */
			$collection->addFieldToFilter("entity_id", array("in"=>$collection));
		}
		
		if(!$collection->count()){
			throw new Mage_Core_Exception(Mage::helper('zolagopo')->__("Specify 1 or more PO"));
		}
		
		$poses = array();
		$carriers = array();
		$currentlyHas = array();
		foreach($collection as $po){
			if($po->getAggregatedId()){
				$currentlyHas[] = $po->getIncrementId();
			}
			$poses[$po->getDefaultPosId()] = $po->getDefaultPosId();
			$carriers[$po->getCurrentCarrier()] = true;
		}
		
		
		$count = count($currentlyHas);
		if($count){
			throw new Mage_Core_Exception(Mage::helper('zolagopo')->__(
					"Purchase Order(s): %s has currently dispatch ref.", 
					implode(",", $currentlyHas)
			));
		}
		
		if(count($poses)!=1){
			throw new Mage_Core_Exception(Mage::helper('zolagopo')->__("Purchase Order(s) have different POSes"));
		}
		if(count($carriers)!=1){
			throw new Mage_Core_Exception(Mage::helper('zolagopo')->__("Purchase Order(s) have different carriers"));
		}
		
		$aggregated = Mage::getModel("zolagopo/aggregated");
		$aggregated->setPosId(current($poses));
		$aggregated->setVendorId($vendor);
		$aggregated->generateName();
		$aggregated->save();
		
		foreach($collection as $po){
			$po->setAggregatedId($aggregated->getId());
			$po->getResource()->saveAttribute($po, "aggregated_id");
		}
		
		return $aggregated->getId();
	}
	
	public function setCondJoined($flag) {
		$this->_condJoined = $flag;
	}
	/**
	 * Add operator filter if session is in operator mode
	 * @return Unirgy_DropshipPo_Model_Mysql4_Po_Collection
	 */
	public function getVendorPoCollection() {
		$collection = parent::getVendorPoCollection();
		if($this->_condJoined){
			return $collection;
		}
		/* @var $collection Unirgy_DropshipPo_Model_Mysql4_Po_Collection */
		$session = Mage::getSingleton('udropship/session');
		/* @var $session Zolago_Dropship_Model_Session */
		if($session->isOperatorMode()){
			Mage::getResourceModel("zolagooperator/operator")->
				addOperatorFilterToPoCollection($collection, $session->getOperator());
		}
		$this->_condJoined = true;
		return $collection;
	}
	
	public function getDhlSettings($vendor, $posId) {
		$dhlSettings = false;
		$posModel = Mage::getModel('zolagopos/pos')->load($posId);
		if ($posModel && $posModel->getId() && $posModel->getUseDhl() && $posModel->getDhlLogin() && $posModel->getDhlPassword() && $posModel->getDhlAccount()) {
			$dhlSettings['login']		= $posModel->getDhlLogin();
			$dhlSettings['account']		= $posModel->getDhlAccount();
			$dhlSettings['password']	= $posModel->getDhlPassword();
		} elseif ($vendor && $vendor->getId() && $vendor->getUseDhl() && $vendor->getDhlLogin() && $vendor->getDhlPassword() && $vendor->getDhlAccount()) {
			$dhlSettings['login']		= $vendor->getDhlLogin();
			$dhlSettings['account']		= $vendor->getDhlAccount();
			$dhlSettings['password']	= $vendor->getDhlPassword();
		}
		
		return $dhlSettings;
	}
	
	/**
	 * @todo handle configurable product
	 * @param Mage_Sales_Model_Order_Item $item
	 * @param Zolago_Po_Model_Po_Item $poItem
	 * @return \Zolago_Po_Helper_Data
	 */
	public function prepareOrderItemByPoItem(Mage_Sales_Model_Order_Item $item, 
		Zolago_Po_Model_Po_Item $poItem) {
		
		$po = $poItem->getPo();
		/* @var $po Zolago_Po_Model_Po */
		
		/**
		 * @todo Make admin way RMA creation available process
		 */
		if(!($po instanceof Zolago_Po_Model_Po)){
			return $this;
		}
		
		$order = $po->getOrder();
		/* @var $order Mage_Sales_Model_Order */
		$tmplItem = $order->getItemsCollection()->getFirstItem();
		/* @var $tmplItem Mage_Sales_Model_Order_Item */
		$store = $order->getStore();
		
		$product = Mage::getModel("catalog/product")->setStoreId($store->getId())
				->load($poItem->getProductId());
		/* @var $product Mage_Catalog_Model_Product */
		
		// Rates
		$globalCurrencyCode  = Mage::app()->getBaseCurrencyCode();
        $baseCurrency = $store->getBaseCurrency();
		
		
		// Taxes 
		$priceInclTax = $poItem->getPriceInclTax();
		
		$taxHelper = Mage::helper('tax');
		/* @var $taxHelper Mage_Tax_Helper_Data */
		$taxConfig = $taxHelper->getConfig();
		/* @var $taxConfig Mage_Tax_Model_Config */
		$taxCalculation = Mage::getSingleton('tax/calculation');
		/* @var $taxCalculation Mage_Tax_Model_Calculation */
		$customerGroup = Mage::getModel("customer/group")->load($po->getOrder()->getCustomerGroupId());
		/* @var $customerGroup Mage_Customer_Model_Group */
		
		$request = $taxCalculation->getRateRequest(
				$po->getShippingAddress(), 
				$po->getBillingAddress(), 
				$customerGroup->getTaxClassId(), 
				$store
		);
		
		$request->setProductClassId($product->getData('tax_class_id'));
		$taxAmount = 0;
		if ($taxRate = $taxCalculation->getRate($request)) {
			$taxAmount  = $store->roundPrice($priceInclTax * (1 - 1 / (($taxRate/100)+1)));
	    }
		$hiddenTaxAmount = 0;
		
		if($poItem->getDiscountPercent()){
			$originTax = $taxAmount;
			$taxAmount *= (1-$poItem->getDiscountPercent()/100);
			$hiddenTaxAmount = $originTax - $taxAmount;
		}
		// Stock item 
		$stockItem = $product->getStockItem();
		
		/**
		* @todo PO item if this has parent PO item use its ORDER item id 
		* as value of parent_item_id of new ORDER item
		*/
		
		$data = array(			
			"order_id"						=> $order->getId(),
			"parent_item_id"				=> null,
			"quote_item_id"					=> null, 
			"store_id"						=> $store->getId(),
			"created_at"					=> null, 
			"updated_at"					=> null,
			"product_id"					=> $product->getId(),
			"product_type"					=> $product->getTypeId(),
			"product_options"				=> null,
			"weight"						=> $poItem->getWeight(),
			"is_virtual"					=> $poItem->getIsVirtual(),
			"sku"							=> $poItem->getSku(),
			"name"							=> $poItem->getName(),
			"description"					=> null,
			"applied_rule_ids"				=> null,
			"additional_data"				=> null,
			"free_shipping"					=> 0,
			"is_qty_decimal"				=> $stockItem ? $stockItem->getIsQtyDecimal() : 0,
			"no_discount"					=> 0,
			"qty_backordered"				=> null,
			"qty_canceled"					=> 0,
			"qty_invoiced"					=> 0,
			"qty_ordered"					=> $poItem->getQty(),
			"qty_refunded"					=> 0,
			"qty_shipped"					=> 0,
			"base_cost"						=> $poItem->getBaseCost(),
			"price"							=> $poItem->getPrice(),
			"base_price"					=> $store->convertPrice($poItem->getPrice()),
			"original_price"				=> $product->getPrice(),
			"base_original_price"			=> $store->convertPrice($product->getPrice()),
			"tax_percent"					=> $taxRate,
			"tax_amount"					=> $taxAmount * $poItem->getQty(),
			"base_tax_amount"				=> $store->convertPrice($taxAmount * $poItem->getQty()),
			"tax_invoiced"					=> 0,
			"base_tax_invoiced"				=> 0,
			"discount_percent"				=> $poItem->getDiscountPercent(), 
			"discount_amount"				=> $poItem->getDiscountAmount(), 
			"base_discount_amount"			=> $store->convertPrice($poItem->getDiscountAmount()),
			"discount_invoiced"				=> 0,
			"base_discount_invoiced"		=> 0,
			"amount_refunded"				=> 0,
			"base_amount_refunded"			=> 0,
			"row_total"						=> $poItem->getRowTotal(),
			"base_row_total"				=> $store->convertPrice($poItem->getRowTotal()),
			"row_invoiced"					=> 0,
			"base_row_invoiced"				=> 0,
			"row_weight"					=> $poItem->getWeight() * $poItem->getQty(),
			"gift_message_id"				=> null,
			"gift_message_available"		=> null,
			"base_tax_before_discount"		=> null,
			"tax_before_discount"			=> null,
			"weee_tax_applied"				=> null,
			"weee_tax_applied_amount"		=> 0,
			"weee_tax_applied_row_amount"	=> 0,
			"base_weee_tax_applied_amount"	=> 0,
			"base_weee_tax_applied_row_amnt"=> 0,
			"weee_tax_disposition"			=> 0,
			"weee_tax_row_disposition"		=> 0,
			"base_weee_tax_disposition"		=> 0,
			"base_weee_tax_row_disposition"	=> 0,
			"ext_order_item_id"				=> null,
			"locked_do_invoice"				=> null,
			"locked_do_ship"				=> null,
			"price_incl_tax"				=> $priceInclTax,
			"base_price_incl_tax"			=> $store->convertPrice($priceInclTax),
			"row_total_incl_tax"			=> $poItem->getRowTotalInclTax(),
			"base_row_total_incl_tax"		=> $store->convertPrice($poItem->getRowTotalInclTax()),
			"hidden_tax_amount"				=> $hiddenTaxAmount * $poItem->getQty(),
			"base_hidden_tax_amount"		=> $store->convertPrice($hiddenTaxAmount * $poItem->getQty()),
			"hidden_tax_invoiced"			=> null,
			"base_hidden_tax_invoiced"		=> null,
			"hidden_tax_refunded"			=> null,
			"base_hidden_tax_refunded"		=> null,
			"is_nominal"					=> $poItem->getIsNominal(),
			"tax_canceled"					=> null,
			"hidden_tax_canceled"			=> null,
			"tax_refunded"					=> null,
			"base_tax_refunded"				=> null,
			"discount_refunded"				=> null,
			"base_discount_refunded"		=> null,
			"udropship_vendor"				=> $po->getUdropshipVendor(),
			"locked_do_udpo"				=> null,
			"qty_udpo"						=> $poItem->getQty(),
			"udpo_seq_number"				=> null,
			"udpo_qty_reverted"				=> null,
			"udpo_qty_used"					=> $poItem->getQty()
		);
		
		$item->addData($data);
		$item->setOrder($order);
		$order->addItem($item);
	}
}
