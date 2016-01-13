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
     * @return Zolago_Adminhtml_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper("zolagoadminhtml");
    }

    public function saveOfflineCustomerData($observer)
    {
        $customer = $observer->getCustomer();
        $data = $observer->getRequest()->getPost();

        if (isset($data['account_offline_data']['loyalty_card_number_1'])) {
            $customer->setData('loyalty_card_number_1', $data['account_offline_data']['loyalty_card_number_1']);
        }
        if (isset($data['account_offline_data']['loyalty_card_number_2'])) {
            $customer->setData('loyalty_card_number_2', $data['account_offline_data']['loyalty_card_number_2']);
        }
        if (isset($data['account_offline_data']['loyalty_card_number_3'])) {
            $customer->setData('loyalty_card_number_3', $data['account_offline_data']['loyalty_card_number_3']);
        }
    }
}
