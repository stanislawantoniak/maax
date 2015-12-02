<?php
class Zolago_Turpentine_Model_Observer {
	/**
	 * Handle caching products
	 * @todo remove after full cache ready
	 * @area: frontend
	 * @event: catalog_controller_product_view
	 * @param Varien_Event_Observer $observer
	 */
	public function productView(Varien_Event_Observer $observer) {
		Mage::register('turpentine_nocache_flag', 0); // allow to cache
	}

    /**
     * Handle caching for help pages
     * @todo remove after full cache ready
     * @area: frontend
     * @event: help_controller_index | help_controller_contact
     * @param Varien_Event_Observer $observer
     */
    public function helpViews(Varien_Event_Observer $observer) {
	    if(is_null(Mage::registry('turpentine_nocache_flag'))) {
		    Mage::register('turpentine_nocache_flag', 0);
	    }// allow to cache
    }

    /**
     * Handle caching for cms pages
     * @todo remove after full cache ready
     * @area: fronted
     * @event: cms_controller_page
     * @param Varien_Event_Observer $observer
     */
    public function cmsView(Varien_Event_Observer $observer) {
        Mage::register('turpentine_nocache_flag', 0); // allow to cache
    }
}