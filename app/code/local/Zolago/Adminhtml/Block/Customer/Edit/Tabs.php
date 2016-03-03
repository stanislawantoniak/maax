<?php

/**
 * admin customer left menu
 * Adding tabs:
 * 1) Client login - data about customer login/logout
 * 2) Recently viewed - data about last seen products
 * 3) Viewed categories - history of viewed pages
 * 4) Beacon data grid - offline history of customer activity
 */
class Zolago_Adminhtml_Block_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs {

    protected function _beforeToHtml() {
        parent::_beforeToHtml();
        $this->addTab('wishlist', array(
            'label'     => Mage::helper('zolagoadminhtml')->__('Wishlist'),
            'class'     => 'ajax',
            'url'       => $this->getUrl('*/*/wishlist', array('_current' => true)),
        ));
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
        $this->addTab('tab_customer_beacon_data', array(
            'label' => Mage::helper('zolagoadminhtml')->__('Offline history of customer activity'),
            'title' => Mage::helper('zolagoadminhtml')->__('Beacon'),
            'url'   => $this->getUrl('*/customer_beacon/data', array('_current' => true)),
            'class' => 'ajax',
        ));
		$this->addTab('tab_customer_coupons', array(
			'label' => Mage::helper('zolagoadminhtml')->__('Customer coupons'),
			'title' => Mage::helper('zolagoadminhtml')->__('Customer coupons'),
			'url'   => $this->getUrl('*/customer_coupon/index', array('_current' => true)),
			'class' => 'ajax',
		));
        $this->addTab('tab_customer_offline_data', array(
            'label' => Mage::helper('zolagoadminhtml')->__('Offline client identification'),
            'title' => Mage::helper('zolagoadminhtml')->__('Offline client identification'),
            'content'   => $this->getLayout()->createBlock('zolagoadminhtml/customer_edit_tab_offline')->initForm()->toHtml(),
            'active'    => Mage::registry('current_customer')->getId() ? false : true
        ));
	    $this->addTab('tab_customer_ghutm', array(
		    'label' => Mage::helper('ghutm')->__('Traffic source'),
		    'title' => Mage::helper('ghutm')->__('Traffic source'),
		    'content'   => $this->getLayout()->createBlock('zolagoadminhtml/customer_edit_tab_ghutm')->initForm()->toHtml(),
		    'active'    => Mage::registry('current_customer')->getId() ? false : true
	    ));
        $this->_updateActiveTab();
        Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml();
        return $this;
    }
}
