<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Posts
    extends Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    const REGISTRY_POSTS = 'mpblog_post_update_posts';

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldSet = $form->addFieldset('post_legend', array('legend' => $this->_helper()->__('Posts')));

        $fieldSet->addField('post_ids', 'hidden', array(
            'name'  => 'post_ids',
        ));

        $form->setUseContainer(false);

        # Define Post Ids to operate with
        $values = $this->_getValues();
        $values['post_ids'] = implode(",", Mage::registry(self::REGISTRY_POSTS));

        $form->setValues($values);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_helper()->__("Posts to Operates");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        # Show this tab
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        # But hide it
        return true;
    }
}
