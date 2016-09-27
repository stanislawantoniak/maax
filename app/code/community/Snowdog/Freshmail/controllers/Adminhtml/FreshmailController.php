<?php

class Snowdog_Freshmail_Adminhtml_FreshmailController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Refresh lists cache
     */
    public function refreshListsAction()
    {
        $cache = Mage::app()->getCacheInstance();
        $cache->remove(Snowdog_Freshmail_Helper_Api::LISTS_CACHE_ID);
        $this->_getSession()->addSuccess(
            Mage::helper('snowfreshmail')
                ->__('Subscription lists have been refreshed.')
        );
        $this->_redirectReferer();
    }

    /**
     * Run sync process
     */
    public function syncAction()
    {
        try {
            if (!Mage::helper('snowfreshmail/api')->isConnected()) {
                $this->_getSession()->addError(
                    Mage::helper('snowfreshmail')->__('Please configure FreshMail connection at first.')
                );
                $this->_redirect('*/system_config/edit', array('section' => 'snowfreshmail'));
                return;
            }
            $totalProcessed = (int) Mage::getSingleton('snowfreshmail/cron')
                ->runSubscribersSyncBatch();
            $message = Mage::helper('snowfreshmail')
                ->__('Processed %s subscribers to FreshMail.', $totalProcessed);
            if ($totalProcessed) {
                $this->_getSession()->addSuccess($message);
            } else {
                $this->_getSession()->addNotice($message);
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }

    /**
     * Check is allowed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('admin/snowfreshmail/refreshLists');
    }
}
