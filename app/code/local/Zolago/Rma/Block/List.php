<?php

class Zolago_Rma_Block_List extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('zolagorma/list.phtml');
    }

	/**
	 * @return bool | array
	 */
    public function getRmaList() {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId) {
            return array();
        }

        return Mage::getModel('zolagorma/rma')->
				getCollection()->
				addAttributeToSort('created_at', 'DESC')->
				addFieldToFilter('customer_id',$customerId)->
				addFieldToFilter('rma_type',Zolago_Rma_Model_Rma::RMA_TYPE_STANDARD);
    }

	/**
	 * @param Zolago_Po_Model_Po_Item $item
	 * @param int $width
	 * @return string
	 */
	public function getItemThumb($item,$width=40) {
		return $item->getProductThumbHelper()->
		resize($width,null)->
		keepFrame(false);
	}

	/**
	 * @param array $options
	 * @return array
	 */
	public function getProductAttributes($options = array()) {
		return isset($options['attributes_info']) ? $options['attributes_info'] : array();
	}

	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return string
	 */
	public function getStatusCustomerText(Zolago_Rma_Model_Rma $rma) {
		return $this->__($rma->getStatusCustomerNotes() ? $rma->getStatusCustomerNotes() : $rma->getRmaStatusName());
	}
}
