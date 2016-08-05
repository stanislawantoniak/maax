<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Author_Edit
    extends Magpleasure_Common_Block_Adminhtml_Widget_Ajax_Form_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'mpblog';
        $this->_controller = 'adminhtml_author';
    }

    public function getHeaderText()
    {
        return $this->_helper()->__('Default Author Info for Blog Pro');
    }

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function onSave($id = null, array $data)
    {
        $userId = false;

        /** @var Mage_Admin_Model_User $user */
        $user = $this->_helper()->getAdminSession()->getUser();

        if ($user) {
            $userId = $user->getId();
        }

        $author = Mage::getModel('mpblog/author');
        if ($userId) {
            $author
                ->load($userId, 'user_id')
                ->addData($data)
                ->setUserId($userId)
                ->save();


        }

        return $this;
    }

    public function onLoad($id = null)
    {
        $userId = false;

        /** @var Mage_Admin_Model_User $user */
        $user = $this->_helper()->getAdminSession()->getUser();

        if ($user) {
            $userId = $user->getId();
            Mage::register('default_author_name', $user->getName(), true);
        }

        if ($userId) {
            $author = Mage::getModel('mpblog/author')->load($userId, 'user_id');
            if ($author->getId()) {
                Mage::register('author_model', $author, true);
            }
        }
        return $this;
    }


}