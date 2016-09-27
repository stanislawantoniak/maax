<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Post_Edit extends Magpleasure_Blog_Block_Adminhtml_Filterable_Edit
{

    /**
     * Frontend URL Model
     *
     * @return Mage_Core_Model_Url
     */
    protected function _getFrontendUrlModel()
    {
        return Mage::getSingleton("core/url");
    }

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'mpblog';
        $this->_controller = 'adminhtml_post';

        $this->_updateButton('save', 'label', $this->_helper()->__('Save'));
        $this->_updateButton('delete', 'label', $this->_helper()->__('Delete'));
        $this->_updateButton('reset', 'onclick', 'setLocation(\'' . $this->getResetUrl() . '\')');


        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        if ($this->_getId()){
            $this->addButton('duplicate', array(
                'title' => $this->_helper()->__("Duplicate"),
                'label' => $this->_helper()->__("Duplicate"),
                'onclick' => "duplicate();",
                'class' => 'scalable save duplicate',
            ), 1, 2);

            $params = $this->_getCommonParams();
            $params['id'] = $this->_getId();

            $duplicateUrl = $this->getUrl('*/*/duplicate', $params);
            $confirmationMessage = $this->_helper()->__("Please confirm duplicating. All data that hasn't been saved will be lost.");
            $confirmationMessage = str_replace("'", "\\'",$confirmationMessage);
            $this->_formScripts[] = "
                function duplicate(){
                    if (confirm('{$confirmationMessage}')){
                        window.location = '{$duplicateUrl}';
                    }
                }
            ";
        }

        $this->addButton('preview', array(
            'title' => $this->_helper()->__("Preview"),
            'label' => $this->_helper()->__("Preview"),
            'onclick' => "preview();",
            'id'    => 'preview_button',
            'class' => 'scalable show-hide',
        ), 1, 3);


        /** @var Mage_Adminhtml_Block_Customer $block */
        $storeId = $store = Mage::app()->getDefaultStoreView()->getId();
        $previewParams = array(
            'url' => Mage::app()->getStore($storeId)->getUrl("mpblog/preview/window"),
            'content_id' => 'full_content',
            'header_id' => 'title',
            'post_thumbnail_id' => 'post_thumbnail_hidden',
            'list_thumbnail_id' => 'list_thumbnail_hidden',
            'button_id' => 'preview_button',
            'width'     => '900',
            'height'     => '700',
        );

        $previewJSON = $this->_helper()->getJSON($previewParams);
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/tab/' + blog_tabsJsTabs.activeTab.id.replace('blog_tabs_', '')+'/');
            }

            function preview(){
                mpBlogPreview.preview();
            }
            var mpBlogPreview = new MpBlogPreview({$previewJSON});
        ";

        $this->setTemplate('mpblog/post/edit.phtml');
    }

    public function getBackUrl()
    {
        $params = $this->_getCommonParams();
        if ($this->_getId()){
            $params['id'] = $this->_getId();
        }

        return $this->getUrl('*/*/back', $params);
    }

    public function getResetUrl()
    {
        $params = $this->_getCommonParams();
        if ($this->_getId()){
            $params['id'] = $this->_getId();
        }

        return $this->getUrl('*/*/reset', $params);
    }

    protected function _getId()
    {
        if ($id = $this->getRequest()->getParam('id')){
            return $id;
        } else {
            return false;
        }
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_post') && Mage::registry('current_post')->getId()) {
            return $this->_helper()->__("Edit Post '%s'", $this->escapeHtml(Mage::registry('current_post')->getTitle()));
        } else {
            return $this->_helper()->__('New Post');
        }
    }
}