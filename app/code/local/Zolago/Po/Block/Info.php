<?php
class Zolago_Po_Block_Info extends Mage_Core_Block_Template
{
    protected $_links = array();

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('zolagopo/info.phtml');
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
        );
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

	/**
     * Retrieve current order model instance
     *
     * @return Zolago_Po_Model_Po
     */
	public function getOrder() {
		return $this->getPo()->getOrder();
	}

	/**
	 * @return Zolago_Po_Model_Po
	 */
    public function getPo()
    {
        return Mage::registry('current_po');
    }

    public function addLink($name, $path, $label)
    {
        $this->_links[$name] = new Varien_Object(array(
            'name' => $name,
            'label' => $label,
            'url' => empty($path) ? '' : Mage::getUrl($path, array('po_id' => $this->getPo()->getId()))
        ));
        return $this;
    }

    public function getLinks()
    {
        $this->checkLinks();
        return $this->_links;
    }

    private function checkLinks()
    {
        $po = $this->getPo();
        if (!$po->hasInvoices()) {
            unset($this->_links['invoice']);
        }
        if (!$po->hasShipments()) {
            unset($this->_links['shipment']);
        }
        if (!$po->hasCreditmemos()) {
            unset($this->_links['creditmemo']);
        }
    }

   

}
