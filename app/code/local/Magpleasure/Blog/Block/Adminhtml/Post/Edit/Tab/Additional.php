<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Post_Edit_Tab_Additional
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

    protected function _getValues()
    {
        if (!$this->_values){

            if (Mage::getSingleton('adminhtml/session')->getPostData()) {
                $this->_values = Mage::getSingleton('adminhtml/session')->getPostData();
                Mage::getSingleton('adminhtml/session')->getPostData(null);
            } elseif (Mage::registry('current_post')) {
                $this->_values = Mage::registry('current_post')->getData();
            }

            /** @var Magpleasure_Blog_Model_Author $author */
            $author = Mage::getModel('mpblog/author');

            if (!$this->getRequest()->getParam("id")){

                $defaults = array(
                    "posted_by" => $author->getDefaultName(),
                    "google_profile" => $author->getDefaultGoogleProfile(),
                    "twitter_profile" => $author->getDefaultTwitterProfile(),
                    "facebook_profile" => $author->getDefaultFacebookProfile(),
                    "notify_on_enable" => 1,
                    "comments_enabled" => 1,
                );

                foreach ($defaults as $key => $value) {

                    if (!isset($this->_values[$key])){
                        $this->_values[$key] = $value;
                    }
                }
            }


            if (!Mage::app()->isSingleStoreMode() && $this->isStoreFilterApplied()){

                $this->_values['stores'] = $this->getAppliedStoreId();

            } elseif (
                Mage::app()->isSingleStoreMode() ||
                (
                    !Mage::app()->isSingleStoreMode() &&
                    !isset($this->_values['stores'])
                )
            ) {

                $this->_values['stores'] = Mage::app()->getDefaultStoreView()->getId();
            }
        }
        return $this->_values;
    }


    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('blog_form_author', array('legend' => $this->_helper()->__('Author')));

        $fieldset->addField('posted_by', 'text', array(
            'label' => $this->_helper()->__("Name"),
            'required' => false,
            'name' => 'posted_by',
        ));


        $example = "https://plus.google.com/109412257237874861202/about";
        $url = "https://support.google.com/webmasters/answer/2539557?hl=en";

        $fieldset->addField('google_profile', 'text', array(
            'label' => $this->_helper()->__("Google Profile"),
            'required' => false,
            'name' => 'google_profile',
            'note'      => $this->_helper()->__("Example: <i>%s</i><br/>Read more here - <a href='%s' target='_blank'>Using rel=author</a>.", $example, $url),
        ));

        $fieldset->addField('facebook_profile', 'text', array(
            'label'     =>$this->_helper()->__('Facebook Profile'),
            'required'  => false,
            'class' => 'validate-uri',
            'name'      => 'facebook_profile',
        ));

        $fieldset->addField('twitter_profile', 'text', array(
            'label'     =>$this->_helper()->__('Twitter Profile'),
            'required'  => false,
            'class'     => 'validate-twitter',
            'name'      => 'twitter_profile',
        ));


        $fieldset = $form->addFieldset('blog_form', array('legend' => $this->_helper()->__('Additional')));

        /** @var Mage_Adminhtml_Model_System_Store $systemStore */
        $systemStore = Mage::getSingleton('adminhtml/system_store');

        $stores = false;

        if (!Mage::app()->isSingleStoreMode()){
            if ($this->isStoreFilterApplied()){
                $stores = array($this->getAppliedStoreId());
            } else {
                $stores = $this->_helper()->getCommon()->getStore()->getFrontendStoreIds();
            }
        }

        /** @var Magpleasure_Blog_Model_Category $category  */
        $category= Mage::getModel('mpblog/category');



        $categoryField = $fieldset->addField('categories', 'multiselect',
            array(
                'label'     => $this->_helper()->__('Posted in'),
                'name'      => 'categories[]',
                'values'    => $category->getCategoryList($stores),
            ));


        # Hint to create category if no one exists
        if (!count($category->getCategoryList($stores))){
            $params = $this->_getCommonParams();
            $params['back_to'] = $this->_helper()->getCommon()->getCore()->urlEncode(
                $this->_helper()->getCommon()->getRequest()->getCurrentManegtoUrl()
            );

            $categoryCreateUrl = $this->getUrl('adminhtml/mpblog_category/new', $params);
            $categoryCreateLabel = $this->_helper()->__("Create New");
            $categoryLink = '[<a href="'.$categoryCreateUrl.'" target="_blank">'.$categoryCreateLabel.'</a>]';

            $categoryField->setData('note', $this->_helper()->__("You have no one category")."&nbsp;".$categoryLink);
        }


        if (!Mage::app()->isSingleStoreMode()){
            if ($this->isStoreFilterApplied()){
                $fieldset->addField('stores', 'hidden',
                    array(
                        'name' => 'stores[]',
                    ));

            } else {

                $fieldset->addField('stores', 'multiselect',
                    array(
                        'label'     => $this->_helper()->__('Visible in'),
                        'required'  => true,
                        'name'      => 'stores[]',
                        'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
                    ));
            }
        } else {
            $fieldset->addField('stores', 'hidden',
                array(
                    'name' => 'stores[]',
                )
            );
        }

        $fieldset->addField('comments_enabled', 'select', array(
            'label'     => $this->_helper()->__("Allow Comments"),
            'required'  => false,
            'name'      => 'comments_enabled',
            'options'   => $this->_helper()
                ->getCommon()
                ->getArrays()
                ->valueLabelToParams(
                    Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray()
                ),
        ));

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
        return $this->_helper()->__("Additional");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_helper()->__("Additional");
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