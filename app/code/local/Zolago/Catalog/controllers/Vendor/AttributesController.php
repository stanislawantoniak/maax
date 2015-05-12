<?php
/**
 * controller for attributes preview
 */
class Zolago_Catalog_Vendor_AttributesController 
    	extends Zolago_Catalog_Controller_Vendor_Product_Abstract {
	
	/**
	 * Index
	 */
	public function indexAction() {
		$this->_renderPage(null, 'udprod_product');
    }
    
    public function get_attributesAction() {
        $attributeId = $this->getRequest()->getParam('attribute_set');
        echo '<option>afdasdfa</option>';
        echo '<option>'.$attributeId.'</opton>';
        die();
    }	
}
