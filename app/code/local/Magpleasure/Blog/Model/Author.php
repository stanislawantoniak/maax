<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Author extends Magpleasure_Blog_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/author');
    }

    /**
     * User
     *
     * @return Mage_Admin_Model_User
     */
    protected function _getUser()
    {
        return Mage::getSingleton('admin/session')->getUser();
    }

    public function getUser()
    {
        return $this->_getUser();
    }

    public function getDefaultName()
    {
        if (!$this->getId()){
            if ($user = $this->_getUser()){
                $this->load($user->getId(), 'user_id');
            }
        }

        return $this->getId() ? $this->getName() : $user->getName();
    }

    public function getDefaultGoogleProfile()
    {
        if (!$this->getId()){
            if ($user = $this->_getUser()){
                $this->load($user->getId(), 'user_id');
            }
        }

        if ($this->getId()){
            return $this->getGoogleProfile();
        }

        return false;
    }

    public function getDefaultTwitterProfile()
    {
        if (!$this->getId()){
            if ($user = $this->_getUser()){
                $this->load($user->getId(), 'user_id');
            }
        }

        if ($this->getId()){
            return $this->getTwitterProfile();
        }

        return false;
    }

    public function getDefaultFacebookProfile()
    {
        if (!$this->getId()){
            if ($user = $this->_getUser()){
                $this->load($user->getId(), 'user_id');
            }
        }

        if ($this->getId()){
            return $this->getFacebookProfile();
        }

        return false;
    }

    public function updateStoreId($storeId)
    {
        if ($this->getData('store_id') != $storeId){
            $this
                ->setData('store_id', $storeId)
                ->save()
            ;
        }

        return $this;
    }
}