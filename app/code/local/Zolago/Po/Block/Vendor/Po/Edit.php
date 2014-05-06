<?php

class Zolago_Po_Block_Vendor_Po_Edit extends Zolago_Po_Block_Vendor_Po_Info
{
	public function __construct(array $args = array()){
		parent::__construct($args);
		$this->_prepareShipments();
	}
	/**
	 * @return Zolago_Po_Model_Po
	 */
    public function getPo(){
		return parent::getPo();
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po $po
	 * @return string
	 */
	public function getCurrentStatus(Unirgy_DropshipPo_Model_Po $po) {
		return Mage::helper("udpo")->getPoStatusName($this->getPo()->getUdropshipStatus());
	}
	
	public function getAllStatuses() {
		
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po $po
	 * @return Zolago_Pos_Model_Pos
	 */
	public function getPos(Unirgy_DropshipPo_Model_Po $po) {
		return $po->getPos();
	}
	
	/**
	 * @return array
	 */
	public function getAllowedStatuses() {
		return Mage::helper('udpo')->getVendorUdpoStatuses();
	}
	
	/**
	 * @param Unirgy_DropshipPo_Model_Po $po
	 * @return array
	 */
	public function getAllowedStatusesForPo(Unirgy_DropshipPo_Model_Po $po) {
		return $this->getAllowedStatuses();
	}
	
	
	
	public function	getItemRedener(Unirgy_DropshipPo_Model_Po_Item $item) {
		$orderItem = $item->getOrderItem();
		$type=$orderItem->getProductType();
		return $this->_getRendererByType($type)->setItem($item);
		
	}
	
	protected function _prepareShipments() {
		$_po = $this->getPo();
		$_order = $_po->getOrder();
		$_vendor = $this->getVendor();
		$_hlp = Mage::helper('udropship');
		$shipping = $_hlp->getShippingMethods();
		$vShipping = $_vendor->getShippingMethods();

		$poShippingMethod = $_po->getUdropshipMethod();
		if (null == $poShippingMethod) {
			$poShippingMethod = $_order->getShippingMethod();
		}

		$uMethod = explode('_', $_order->getShippingMethod(), 2);
		if ($uMethod[0]=='udsplit') {
			$udMethod = Mage::helper('udropship')->mapSystemToUdropshipMethod(
				$poShippingMethod,
				$_vendor
			);
			$uMethodCode = $udMethod->getShippingCode();
		} else {
			$uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : '';
		}

		$method = explode('_', $poShippingMethod, 2);
		$carrierCode = !empty($method[0]) ? $method[0] : $_vendor->getCarrierCode();

		$curShipping = $shipping->getItemByColumnValue('shipping_code', $uMethodCode);
		$methodCode  = !empty($method[1]) ? $method[1] : '';

		$labelCarrierAllowAll = Mage::getStoreConfig('udropship/vendor/label_carrier_allow_all', $_order->getStoreId());
		$labelMethodAllowAll = Mage::getStoreConfig('udropship/vendor/label_method_allow_all', $_order->getStoreId());

		if ($curShipping && $labelMethodAllowAll) {
			$curShipping->useProfile($_vendor);
			$_carriers = array($carrierCode=>0);
			if ($labelCarrierAllowAll) {
				$_carriers = array_merge($_carriers, $curShipping->getAllSystemMethods());
			}
			$availableMethods = array();
			foreach ($_carriers as $_carrierCode=>$_dummy) {
				$_availableMethods = $_hlp->getCarrierMethods($_carrierCode, true);
				$carrierTitle = Mage::getStoreConfig("carriers/$_carrierCode/title", $_order->getStoreId());
				foreach ($_availableMethods as $mCode => $mLabel) {
					$_amDesc = $carrierTitle.' - '.$mLabel;
					$_amCode = $_carrierCode.'_'.$mCode;
					$availableMethods[$_amCode] = $_amDesc;
				}
			}
			$curShipping->resetProfile();
		} elseif ($curShipping && isset($vShipping[$curShipping->getId()])) {
			$curShipping->useProfile($_vendor);
			$methodCode  = !empty($method[1]) ? $method[1] : $curShipping->getSystemMethods($vShipping[$curShipping->getId()]['carrier_code']);
			$availableMethods = array();
			if (!$labelCarrierAllowAll || Mage::helper('udropship')->isUdsprofileActive()) {
				foreach ($vShipping as $_sId => $__vs) {
					foreach ($__vs as $_vs) {
						if ($carrierCode != $_vs['carrier_code'] && !$labelCarrierAllowAll || !($_s = $shipping->getItemById($_sId)) || !($_vs['method_code'])) continue;
						$_amCode = $_vs['carrier_code'].'_'.$_vs['method_code'];
						$carrierMethods = Mage::helper('udropship')->getCarrierMethods($_vs['carrier_code']);
						if (!isset($carrierMethods[$_vs['method_code']])) continue;
						$_amDesc = Mage::getStoreConfig('carriers/'.$_vs['carrier_code'].'/title', $_order->getStoreId())
							.' - '.$carrierMethods[$_vs['method_code']];
						$availableMethods[$_amCode] = $_amDesc;
					}
				}
			} else {
				foreach ($vShipping as $_sId => $__vs) {
					if (($_s = $shipping->getItemById($_sId))) {
						$allSystemMethods = $_s->getAllSystemMethods();
						foreach ($allSystemMethods as $_smCarrier => $__sm) {
							foreach ($__sm as $_smMethod) {
								$_amCode = $_smCarrier.'_'.$_smMethod;
								$carrierMethods = Mage::helper('udropship')->getCarrierMethods($_smCarrier);
								if (!isset($carrierMethods[$_smMethod])) continue;
								$_amDesc = Mage::getStoreConfig('carriers/'.$_smCarrier.'/title', $_order->getStoreId())
									.' - '.$carrierMethods[$_smMethod];
								$availableMethods[$_amCode] = $_amDesc;
							}
						}
					}
				}
			}
			$curShipping->resetProfile();
		}

		$labelCarrierAllowAlways = Mage::getStoreConfig('udropship/vendor/label_carrier_allow_always', $_order->getStoreId());
		if (!is_array($labelCarrierAllowAlways)) {
			$labelCarrierAllowAlways = array_filter(explode(',', $labelCarrierAllowAlways));
		}
		foreach ($labelCarrierAllowAlways as $lcaaCode) {
			$lcaaCarrierMethods = Mage::helper('udropship')->getCarrierMethods($lcaaCode, true);
			foreach ($lcaaCarrierMethods as $lcaaMethodCode=>$lcaaMethodTitle) {
				$lcaaFullMethodCode = $lcaaCode.'_'.$lcaaMethodCode;
				$lcaaDesc = Mage::getStoreConfig('carriers/'.$lcaaCode.'/title', $_order->getStoreId())
					.' - '.$lcaaMethodTitle;
				$availableMethods[$lcaaFullMethodCode] = $lcaaDesc;
			}
		}

		if (count($method)>1) {
			$_poCarrierMethods = Mage::helper('udropship')->getCarrierMethods($method[0]);
			if (isset($_poCarrierMethods[$method[1]])) {
				$availableMethods[$poShippingMethod] = Mage::getStoreConfig('carriers/'.$method[0].'/title', $_order->getStoreId())
					.' - '.$_poCarrierMethods[$method[1]];
			}
		}
		$this->setAvailableMethods($availableMethods);
		$this->setCurrentShipping($curShipping);
		$this->setShippingMethod($poShippingMethod);
		
		$collection =  Mage::getResourceModel('sales/order_shipment_collection')->
				addAttributeToFilter('udpo_id', $_po->getId())->
				addAttributeToFilter("udropship_status", 
					array("nin"=>array(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED))
		);
		
		$collection->setOrder("created_at", "DESC");
		$this->setShipmentsCollection($collection);
		$this->setCustomCurrentShipping($collection->getFirstItem());

	}
	
	public function getCurrentTracking(Mage_Sales_Model_Order_Shipment $shipment = null) {
		if($shipment instanceof  Mage_Sales_Model_Order_Shipment && $shipment->getId()){
			$collection = $shipment->getTracksCollection()->setOrder("created_at", "DESC");
			return $collection->getFirstItem();
		}
		return null;
	}
	
	public function canUseCarrier() {
		return $this->canPosUseDhl();
	}
	
	public function canPosUseDhl() {
		return Mage::helper('zolagodhl')->isDhlEnabledForPos($this->getPo()->getDefaultPos());
	}
	
	public function getMethodName($poShippingMethod) {
		foreach($this->getAvailableMethods() as $_amCode => $_amDesc){
			if ($poShippingMethod==$_amCode){
				return $_amDesc;
			}
		}
		return '';
	}
	
	public function getPoUrl($action, $params=array()) {
		$params += array(
			"id"=> $this->getPo()->getId(),
			"form_key" => Mage::getSingleton('core/session')->getFormKey()
		);
		return $this->getUrl("*/*/$action", $params);
	}
	
	public function getHelpdeskUrl(Zolago_Po_Model_Po $po) {
		//filter_customer_name
		return $this->getUrl("udqa/vendor/index", array(
			"_query"=>array("filter_customer_name"=>$po->getOrder()->getCustomerName())
		));
	}
	
	public function getAllMessagesCount(Zolago_Po_Model_Po $po) {
		if(!$this->hasData('all_messages_count')){
			$collection = Mage::getResourceModel('udqa/question_collection');
			/* @var $collection Unirgy_DropshipVendorAskQuestion_Model_Mysql4_Question_Collection */
			$collection->addApprovedAnswersFilter();
			$collection->addVendorFilter($this->getVendor());
			$collection->addFieldToFilter("main_table.customer_name", array("like"=>$this->getPo()->getOrder()->getCustomerName()));
			$this->setData("all_messages_count", $collection->count());
		}
		return $this->getData("all_messages_count");
	}
	
	public function getUnreadMessagesCount(Zolago_Po_Model_Po $po) {
		if(!$this->hasData('unread_messages_count')){
			$collection = Mage::getResourceModel('udqa/question_collection');
			/* @var $collection Unirgy_DropshipVendorAskQuestion_Model_Mysql4_Question_Collection */
			$collection->addApprovedAnswersFilter();
			$collection->addVendorFilter($this->getVendor());
			$collection->addFieldToFilter("main_table.customer_name", array("like"=>$this->getPo()->getOrder()->getCustomerName()));
			$collection->addFieldToFilter("main_table.answer_text", array("null"=>true));
			$this->setData("unread_messages_count", $collection->count());
		}
		return $this->getData("unread_messages_count");
	}
	
	/**
	 * @param type $type
	 * @return Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract
	 */
	protected function _getRendererByType($type) {
		$renderPath = "zolagopo/vendor_po_item_renderer_";
		switch ($type) {
			case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE: 
				/*@todo add other types*/
				$renderPath.=$type;
			break;
			default:
				$renderPath.=Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
			break;
		}
		return $this->getLayout()->createBlock($renderPath);
	}
  
	
	
}
