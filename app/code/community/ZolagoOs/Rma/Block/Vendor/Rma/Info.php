<?php
/**
  
 */

class ZolagoOs_Rma_Block_Vendor_Rma_Info extends Mage_Sales_Block_Items_Abstract
{
    protected function _construct()
    {
        Mage_Core_Block_Template::_construct();
        $this->addItemRender('default', 'sales/order_item_renderer_default', 'urma/sales/order/rma/items/renderer/default.phtml');
    }

    public function getRma()
    {
        if (!$this->hasData('rma')) {
            $id = (int)$this->getRequest()->getParam('id');
            $rma = Mage::getModel('urma/rma')->load($id);
            $rma->setGroupItemsFlag(true);
            $this->setData('rma', $rma);
            Mage::helper('udropship')->assignVendorSkus($rma);
        }
        return $this->getData('rma');
    }

    public function getCarriers()
    {
        $carriers = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers(
            $this->getRma()->getStoreId()
        );
        $carriers['custom'] = Mage::helper('sales')->__('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }

    public function getCarrierTitle($code)
    {
        if ($carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code)) {
            return $carrier->getConfigData('title');
        }
        else {
            return Mage::helper('sales')->__('Custom Value');
        }
        return false;
    }

}
