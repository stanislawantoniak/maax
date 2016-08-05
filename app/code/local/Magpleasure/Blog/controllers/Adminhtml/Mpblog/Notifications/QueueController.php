<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Adminhtml_Mpblog_Notifications_QueueController
    extends Magpleasure_Blog_Controller_Adminhtml_Filterable
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $aclRoute = 'cms/mpblog/notifications/queue';
        $this
            ->_getSession()
            ->setControlRoutePath($aclRoute)
        ;
        return Mage::getSingleton('admin/session')->isAllowed($aclRoute);
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/mpblog/notifications/queue');

        return $this;
    }

    public function indexAction()
    {
        $this->_prepareStoreFilter();

        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Delete comment
     * @param int|string $id
     * @return boolean
     */
    protected function _delete($id)
    {
        $notification = Mage::getModel('mpblog/comment_notification')->load($id);
        if ($notification->getId()){
            try{
                $notification->delete();
                return true;
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    protected function _updateStatus($id, $status)
    {
        if ($id){
            try {
                $notification = Mage::getModel('mpblog/comment_notification')->load($id);
                $notification->setStatus($status);
                $notification->save();
                return true;
            } catch (Exception $e){
                return false;
            }
        }
    }

    public function massStatusAction()
    {
        $notifications = $this->getRequest()->getPost('notifications');
        $status = $this->getRequest()->getPost('status');
        if ($notifications){
            $success = 0;
            $error = 0;
            foreach ($notifications as $notificationId){
                if ($this->_updateStatus($notificationId, $status)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success){
                $this->_getSession()->addSuccess($this->_helper()->__("%s notifications successfully updated.", $success));
            }
            if ($error){
                $this->_getSession()->addError($this->_helper()->__("%s notifications was not updated.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $notifications = $this->getRequest()->getPost('notifications');
        if ($notifications){
            $success = 0;
            $error = 0;
            foreach ($notifications as $notificationId){
                if ($this->_delete($notificationId)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success){
                $this->_getSession()->addSuccess($this->_helper()->__("%s notifications successfully deleted.", $success));
            }
            if ($error){
                $this->_getSession()->addError($this->_helper()->__("%s notifications was not deleted.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    protected function _sendNotification($id, $email = null)
    {
        try {
            $notification = Mage::getModel('mpblog/comment_notification')->load($id);

            if ($notification->getId()){
                $notification->send($email);
                return true;
            } else {
                return false;
            }

            return true;
        } catch (Exception $e){
            return false;
        }
        return true;
    }

    protected function _cancelSend($id)
    {
        try {
            $notification = Mage::getModel('mpblog/comment_notification')->load($id);

            if ($notification->getId()){

                $notification->cancel();
                return true;
            } else {
                return false;
            }

            return true;
        } catch (Exception $e){
            return false;
        }
        return true;
    }

    public function massSendTestAction()
    {
        $notifications = $this->getRequest()->getPost('notifications');
        $email = $this->getRequest()->getPost('email');

        $success = $error = 0;
        foreach ($notifications as $notificationId){
            if ($this->_sendNotification($notificationId, $email)){
                $success++;
            } else {
                $error++;
            }
        }
        if ($success){
            $this->_getSession()->addSuccess($this->_helper()->__("%s test emails were successfully sent.", $success));
        }
        if ($error){
            $this->_getSession()->addError($this->_helper()->__("%s test emails weren't sent.", $error));
        }
        $this->_redirectReferer();
    }

    public function massSendNowAction()
    {
        $notifications = $this->getRequest()->getPost('notifications');

        $success = $error = 0;
        foreach ($notifications as $notificationId){
            if ($this->_sendNotification($notificationId)){
                $success++;
            } else {
                $error++;
            }
        }
        if ($success){
            $this->_getSession()->addSuccess($this->_helper()->__("%s notifications were successfully sent.", $success));
        }
        if ($error){
            $this->_getSession()->addError($this->_helper()->__("%s notifications weren't sent.", $error));
        }
        $this->_redirectReferer();
    }

    public function massCancelAction()
    {
        $notifications = $this->getRequest()->getPost('notifications');

        $success = $error = 0;
        foreach ($notifications as $notificationId){
            if ($this->_cancelSend($notificationId)){
                $success++;
            } else {
                $error++;
            }
        }
        if ($success){
            $this->_getSession()->addSuccess($this->_helper()->__("%s notifications were successfully canceled.", $success));
        }
        if ($error){
            $this->_getSession()->addError($this->_helper()->__("%s notifications weren't canceled.", $error));
        }
        $this->_redirectReferer();
    }

}