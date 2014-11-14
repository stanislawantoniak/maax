<?php

class Zolago_Catalog_Block_Vendor_Product_Grid extends Mage_Core_Block_Template {

	

	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');
        $this->getMassactionBlock()->setTemplate("zolagocatalog/widget/grid/massaction.phtml");

        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('zolagocatalog')->__('Disable Products'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true, 'status' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED)),
        ));
        $this->getMassactionBlock()->addItem('review', array(
            'label'=> Mage::helper('zolagocatalog')->__('Launch products'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true, 'review' => true)),
        ));
        return $this;
    }

	
	/**
	 * @return Zolago_Catalog_Model_Vendor_Product_Grid
	 */
	public function getGridModel() {
		return Mage::getSingleton('zolagocatalog/vendor_product_grid');
	}
	
	/**
	 * @return array
	 */
	public function getColumns() {
		return $this->getGridModel()->getColumns();
	}
}
