<?php
class Zolago_Po_Block_Vendor_Po_Edit_ShippingMethod
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
	implements Zolago_Po_Block_Vendor_Po_Edit_Address_Interface
{

	protected $_shippingTypes = array(
		Mage_Sales_Model_Order_Address::TYPE_SHIPPING, 
		Zolago_Po_Model_Po::TYPE_POSHIPPING
	);
	
	public function getFormUrl() {
		return $this->getPoUrl("saveShippingMethod", array("type"=>$this->getType()));
	}
	
	public function isSameAsOrigin($type) {
		if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
			return $this->getPo()->isShippingSameAsOrder();
		}elseif($type==Mage_Sales_Model_Order_Address::TYPE_BILLING){
			return $this->getPo()->isBillingSameAsOrder();
		}
		return true;
	}
	
	public function isBilling() {
		return $this->getType()==Mage_Sales_Model_Order_Address::TYPE_BILLING;
	}
	
	public function isShipping() {
		return $this->getType()==Mage_Sales_Model_Order_Address::TYPE_SHIPPING;
	}
	
	public function getType() {
		if(in_array($this->getAddress()->getAddressType(), $this->_shippingTypes)){
			return Mage_Sales_Model_Order_Address::TYPE_SHIPPING;
		}else{
			return Mage_Sales_Model_Order_Address::TYPE_BILLING;
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getAvailableShippingMethods()
	{
		$collection = Mage::getModel("udropship/shipping")->getCollection();
		$collection->joinDeliveryType();

		$methods = array();
		foreach ($collection as $collectionItem) {
			$methods[$collectionItem->getDeliveryCode()] = array(
				'title' => $collectionItem->getShippingTitle(),
				'delivery_code' => $collectionItem->getDeliveryCode(),
				'udropship_method' => $collectionItem->getUdropshipMethod()
			);
		}
		return $methods;
	}

	/**
	 *
	 * @return array
	 */
	public function getEditableShippingMethods()
	{
		$forms = array();
		$methods = $this->getAvailableShippingMethods();

		//Construct edit delivery method form
		if(empty($methods))
			return $methods;

		foreach($methods as $code => $method){
			$forms['methods'][$code] = $method;
			switch($code){
				case GH_Inpost_Model_Carrier::CODE :
					$forms['tabs'][GH_Inpost_Model_Carrier::CODE]['methods'][] = $code;
					$forms['tabs'][GH_Inpost_Model_Carrier::CODE]['template'] = 'vendor_po_edit_shipping_method_ghinpost';
					$forms['methods'][$code]['form_link'] = GH_Inpost_Model_Carrier::CODE;
					break;
				case Orba_Shipping_Model_Packstation_Pwr::CODE :
					$forms['tabs'][Orba_Shipping_Model_Packstation_Pwr::CODE]['methods'][] = $code;
					$forms['tabs'][Orba_Shipping_Model_Packstation_Pwr::CODE]['template'] = 'vendor_po_edit_shipping_method_zospwr';
					$forms['methods'][$code]['form_link'] = Orba_Shipping_Model_Packstation_Pwr::CODE;
					break;
				default:
					$forms['tabs']['default']['methods'][] = $code;
					$forms['tabs']['default']['template'] = 'vendor_po_edit_shipping_method_default';
					$forms['methods'][$code]['form_link'] = 'default';

			}
		}

		return $forms;
	}
}
