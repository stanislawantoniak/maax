<?php

class Snowdog_Freshmail_Adminhtml_Freshmail_RequestController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Requests grid
     */
    public function indexAction()
    {
        $lastHeartbeat = Mage::helper('snowfreshmail')->getLastHeartbeat();
        if (is_null($lastHeartbeat)) {
            $this->_getSession()->addError(Mage::helper('snowfreshmail')->__('No cron heartbeat found. Check if cron is configured correctly.'));
        } else {
            $timespan = Mage::helper('snowfreshmail')->dateDiff($lastHeartbeat);
            if ($timespan <= 5 * 60) {
                $this->_getSession()->addSuccess(Mage::helper('snowfreshmail')->__('Last cron heartbeat: %s minute(s) ago', round($timespan / 60)));
            } elseif ($timespan > 5 * 60 && $timespan <= 60 * 60) {
                $this->_getSession()->addNotice(Mage::helper('snowfreshmail')->__('Last cron heartbeat is older than %s minutes.', round($timespan / 60)));
            } else {
                $this->_getSession()->addError(Mage::helper('snowfreshmail')->__('Last cron heartbeat is older than one hour. Please check your settings and your configuration!'));
            }
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Request details
     */
    public function detailsAction()
    {
        $this->_title($this->__('Freshmail'))
            ->_title($this->__('Request Logs'))
            ->_title($this->__('View details'));

        $id = $this->getRequest()->getParam('request_id');
        $model = Mage::getModel('snowfreshmail/api_request');
        $model->load($id);
        if (!$model->getId()) {
            $this->_redirect('*/*/');
            return;
        }
        Mage::register('current_request', $model);

        $this->loadLayout();
        $this->_setActiveMenu('snowfreshmail/request');
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/snowfreshmail');
    }
}
