<?php
/**
 * source for sizetable attribute
 */
class Zolago_Sizetable_Model_Source_Attribute
    extends Zolago_Catalog_Model_Product_Source_Abstract {
    protected $_options;
    public function getAllOptions() {
        if (!$this->_options) {
            $out = array('0' => Mage::helper('catalog')->__('None'));
            $collection = Mage::getModel('zolagosizetable/sizetable')->getCollection();
            $collection->addOrder('name','ASC');
            foreach ($collection as $item) {
                $out[$item->getSizetableId()] = $item->getName();
            }
            $this->_options = $out;
        }
        return $this->_options;
    }
}