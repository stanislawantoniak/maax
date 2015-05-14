<?php
/**
 * controller for attributes preview
 */
class Zolago_Catalog_Vendor_AttributesController 
    	extends Zolago_Catalog_Controller_Vendor_Product_Abstract {
	
	
    /**
     * store assigned to vendor
     */

    protected function _getStore() {
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        return $vendor->getLabelStore();
    }
	/**
	 * Index
	 */
	public function indexAction() {
		$this->_renderPage(null, 'udprod_product');
    }
    
    
    /**
     * attributes list by attribute set
     */

    public function get_attributesAction() {
        $attributeSetId = $this->getRequest()->getParam('attribute_set');
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
        ->addFieldToFilter("grid_permission", array("in"=>array(
            Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION,
            Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::INLINE_EDITION,
        )))
        ->setAttributeSetFilter($attributeSetId)
        ->getItems();
        $_helper = Mage::helper('zolagocatalog');
        $list = array();
        $storeId = $this->_getStore();
        foreach ($attributes as $item) {
            $list[$item->getId()] = array (
                'label' => $item->getStoreLabel($storeId),
                'type' => $item->getFrontendInput(),
                'required' => $item->getIsRequired()? 'required':'not required',
                );
        }
        asort($list);
        $out = '';
        foreach ($list as $key=>$item) {
            if ($item['type'] == 'text') {
                $key = 0;
            }
            $out .= '<option value="'.$key.'">'.$item['label'].' ['.$_helper->__($item['type']).', '.$_helper->__($item['required']).']</option>';
        }
        if (!$out) {
            $out = '<option value="0">'.Mage::helper('zolagocatalog')->__('-- none --').'</option>';
        }
        echo $out;
        die();
    }	
    
    /**
     * attribute values list
     */

    public function get_valuesAction() {
        $storeId = $this->_getStore();
        $attributeId = $this->getRequest()->getParam('attribute');
       $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
       $collection = $attribute ->setStoreId($storeId)->getSource()->getAllOptions(false); 
        $list = array();
        foreach ($collection as $item) {
            $list[] = $item['label'];
        }
        sort($list);
        $out = '';
        foreach ($list as $item) {
            $out .= $item.'<br/>';
        }            
        if (!$out) {
            $out = Mage::helper('zolagocatalog')->__('-- none --');
        }        
        echo $out;
        die();
    }
    
    /**
     * suggestion new attribute value
     */

    public function ask_valueAction() {
        $template = Mage::getStoreConfig('udropship/vendor/ask_attribute_email_template');
        $attrId = $this->getRequest()->getParam('attrId');
        $value = $this->getRequest()->getParam('value');
        echo $template.' '.$attrId.' '.$value;
        // todo
    }
}
