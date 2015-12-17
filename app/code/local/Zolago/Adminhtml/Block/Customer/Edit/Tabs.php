<?php

/**
 * admin customer left menu
 * Adding tabs:
 * 1) Client login - data about customer login/logout
 * 2) Recently viewed - data about last seen products
 * 3) Viewed categories - history of viewed pages
 */
class Zolago_Adminhtml_Block_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs {

    protected function _beforeToHtml() {
        parent::_beforeToHtml();
        $this->addTab('tab_customer_activity_login', array(
            'label'     => Mage::helper('zolagoadminhtml')->__('Client login'),
            'title'     => Mage::helper('zolagoadminhtml')->__('client login'),
            'url'       => $this->getUrl('*/customer_activity/login', array('_current' => true)),
            'class'     => 'ajax',
        ));
        $this->addTab('tab_customer_activity_recently_viewed', array(
            'label'     => Mage::helper('zolagoadminhtml')->__('Recently viewed'),
            'title'     => Mage::helper('zolagoadminhtml')->__('Recently viewed'),
            'url'       => $this->getUrl('*/customer_activity/recentlyviewed', array('_current' => true)),
            'class'     => 'ajax',
        ));
        $this->addTab('tab_customer_activity_viewed_categories', array(
            'label' => Mage::helper('zolagoadminhtml')->__('Viewed categories'),
            'title' => Mage::helper('zolagoadminhtml')->__('Viewed categories'),
            'url'   => $this->getUrl('*/customer_activity/viewedcategories', array('_current' => true)),
            'class' => 'ajax',
        ));
        $this->_updateActiveTab();
        Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml();
        return $this;
    }
}
