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
	
	/**
	 * @see https://github.com/SitePen/dgrid/blob/master/doc/components/mixins/ColumnSet.md
	 * @return array
	 */
	public function getJsonColumns() {
		$out = array();
		foreach($this->getColumns() as $key=>$column){
			$out[]=$this->mapColumn($column);
		}
		return Mage::helper("core")->jsonEncode($out);
	}
	
		
	/**
	 * 
	 {
		label: Translator.translate("Name"),
		field: "name",
		children: [
			{
				renderHeaderCell: filter("text", "name"),
				sortable: false, 
				field: "name",
				className: "filterable",
			}
		]
	  }
	}
	 */
	
	protected function mapColumn(Varien_Object $columnObject) {
		$attribute = null;
		if($columnObject->getAttribute()){
			$attribute = $columnObject->getAttribute();
		}
		
		return array(
			"label" => $columnObject->getHeader(),
			"field" => $columnObject->getIndex(),
			"fixed" => $columnObject->getFixed(),
			"children" => array(
				array(
					//"renderHeaderCell"  => array("text", $columnObject->getIndex()),
					"sortable"			=> false,
					"field"				=> $columnObject->getIndex(),
					"className"			=> "filterable"
				)
			)
		);
	}
}
