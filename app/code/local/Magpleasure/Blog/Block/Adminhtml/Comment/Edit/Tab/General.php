<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Comment_Edit_Tab_General
    extends Magpleasure_Blog_Block_Adminhtml_Filterable_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_values = array();

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _isAnswer()
    {
        return !!Mage::registry('comment_for_answer');
    }

    /**
     * Retrieves user
     *
     * @return bool|Mage_Admin_Model_User
     */
    public function getUser()
    {
        /** @var Mage_Admin_Model_Session $session  */
        $session = Mage::getSingleton('admin/session');
        if ($session->isLoggedIn()) {
            return $session->getUser();
        }
        return false;
    }


    protected function _getValues()
    {
        if (Mage::getSingleton('adminhtml/session')->getCommentData()) {
            $this->_values = Mage::getSingleton('adminhtml/session')->getCommentData();
            Mage::getSingleton('adminhtml/session')->setCommentData(null);
        } elseif (Mage::registry('current_comment')) {
            $this->_values = Mage::registry('current_comment')->getData();
        }

        if ($this->_isAnswer()){

            /** @var $comment Magpleasure_Blog_Model_Comment */
            $comment = Mage::registry('comment_for_answer');

            /** @var $adminSession Mage_Admin_Model_Session */
            $adminSession = Mage::getSingleton('admin/session');

            $this->_values['reply_to'] = $comment->getId();
            $this->_values['post_id'] = $comment->getPostId();
            $this->_values['store_id'] = $comment->getStoreId();
            $this->_values['status'] = Magpleasure_Blog_Model_Comment::STATUS_APPROVED;

            # Hide store field if filter applied
            if ($this->isStoreFilterApplied()){
                $this->_values['store_id'] = $this->getAppliedStoreId();
            }

            # Default admin name
            if (!isset($this->_values['name'])){

                /** @var Magpleasure_Blog_Model_Author $author */
                $author = Mage::getModel('mpblog/author');
                $this->_values['name'] = $author->getDefaultName();
            }
        }

        return $this->_values;
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('blog_form', array('legend' => $this->_helper()->__('General')));

        if ($this->_isAnswer()){
            $fieldset->addField('post_id', 'hidden', array(
                'name' => 'post_id',
            ));
            $fieldset->addField('reply_to', 'hidden', array(
                'name' => 'reply_to',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'label' => $this->_helper()->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $fieldset->addField('email', 'text', array(
            'label' => $this->_helper()->__('Email'),
            'required' => false,
            'name' => 'email',
        ));

        $fieldset->addField('message', 'textarea', array(
            'label' => $this->_helper()->__('Comment'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'message',
        ));

        /** @var Magpleasure_Blog_Model_Comment $comment  */
        $comment = Mage::getSingleton('mpblog/comment');

        $fieldset->addField('status', 'select',
            array(
                'name'      => 'status',
                'label'     => $this->_helper()->__('Status'),
                'values'    => $comment->toOptionArray(),
        ));

        if (!Mage::app()->isSingleStoreMode()){

            if ($this->isStoreFilterApplied()){

                $fieldset->addField('store_id', 'hidden',
                    array(
                        'name' => 'store_id',
                    ));

            } else {

                $fieldset->addField('store_id', 'select',
                    array(
                        'label'     => $this->_helper()->__('Posted From'),
                        'required'  => true,
                        'name'      => 'store_id',
                        'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
                    ));
            }
        }

        $form->setValues($this->_getValues());

        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_helper()->__("General");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_helper()->__("General");
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}