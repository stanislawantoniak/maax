<?php

/**
 * Class Zolago_Adminhtml_Model_Observer
 */
class Zolago_Adminhtml_Model_Observer
{

    /**
     * Add mass action (push to SOLR) into admin product grid
     * @param $observer
     */
    public function addPushToSolrMassaction($observer)
    {
        /** @var Zolago_Adminhtml_Block_Catalog_Product_Grid $block */
        $block = $observer->getBlock();
        $helper = $this->getHelper();

        $block->getMassactionBlock()->addItem('push_to_solr', array(
            'label' => $helper->__('Push to solr queue'),
            'url' => $block->getUrl('*/catalog_product_action_solr/push', array('_current' => true)),
        ));
    }
    /**
     * Add mass action (push to recalculate price queue) into admin product grid
     * @param $observer
     */
    public function addPushToPriceQueueMassaction($observer)
    {
        /** @var Zolago_Adminhtml_Block_Catalog_Product_Grid $block */
        $block = $observer->getBlock();
        $helper = $this->getHelper();

        $block->getMassactionBlock()->addItem('push_to_price_queue', array(
            'label' => $helper->__('Push to price queue'),
            'url' => $block->getUrl('*/catalog_product_action_price/push', array('_current' => true)),
        ));
    }

    /**
     * @return Zolago_Adminhtml_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper("zolagoadminhtml");
    }

	public function saveOfflineCustomerData($observer) {
		$customer = $observer->getCustomer();
		$data = $observer->getRequest()->getPost();
		if (isset($data['account_offline_data'])) {
			foreach ($data['account_offline_data'] as $key => $value) {
				$customer->setData($key, $value);
			}
		}
	}
}
