<?php

class Zolago_Po_Block_Rma extends Mage_Core_Block_Template
{
    protected $_poList;
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('zolagorma/po/list.phtml');
    }
    
    public function setPoList($collection) {
        $this->_poList = $collection;
    }
    public function getPoList() {
        if (empty($this->_poList)) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            if (!$customerId) {
                return array();
            }
            $collection = Mage::getResourceModel('zolagopo/po_collection');
            $collection->addFieldToFilter('main_table.customer_id',$customerId);
            $collection->addFieldToFilter('udropship_status',ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_DELIVERED);
			$collection->setOrder('created_at', 'DESC');

			
            // remove po without shipment
            $out = array();
            foreach ($collection as $po) {
				/* @var $po Zolago_Po_Model_Po */
                if ($po->getLastNotCanceledShipment() && $po->canBeReturned()) {
                    $this->_poList[] = $po;
                }
            }
        }
        return $this->_poList;
        
    }
    public function getItemList($po) {
        if (!$po) {
            return array();
        }
        $items = $po->getItemsCollection();
        $out = Mage::helper('zolagorma')->getItemList($items);
        return $out;
    }
	public function getProductAttributes($options = array()) {
		return isset($options['attributes_info']) ? $options['attributes_info'] : $options;
	}
	public function getThumbnailUrl($item) {
		return Mage::getModel('catalog/product')->load($item->getProductId())->getThumbnailUrl();
	}
	/**
	 * @param @param Zolago_Po_Model_Po_Item | int $item $item
	 * @return Zolago_Po_Model_Po_Item
	 */
	protected function _getPoItem($item) {
		if($item instanceof Zolago_Po_Model_Po_Item){
			return $item;
		}
		if(!($item instanceof Zolago_Po_Model_Po_Item)){
			$item = $this->getPo()->getItemById($item);
		}
		if(!($item instanceof Zolago_Po_Model_Po_Item)){
			$item = Mage::getModel("udpo/po_item")->load($item);
		}
		return $item;
	}
	/**
	 * @param int $item
	 * @param int|null $width
	 * @param int|null $height
	 * @return string
	 */
	public function getPoItemThumb($item, $width=60, $height=null) {
		return $this->_getPoItem($item)->getProductThumbHelper()->
		resize($width, $height)->
		keepFrame(false);
	}
}
