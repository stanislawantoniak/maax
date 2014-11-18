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
	
	
	protected function mapColumn(Varien_Object $columnObject) {
		$attribute = null;
		if($columnObject->getAttribute()){
			$attribute = $columnObject->getAttribute();
		}
		$classes = array(
			"type-" . $columnObject->getType()
		);
		
		$out = array(
			"label" => $columnObject->getHeader(),
			"field" => $columnObject->getIndex(),
			"type" => $columnObject->getType(), 
			"fixed" => $columnObject->getFixed(),
			"sortable" => $columnObject->getSortable(),
		);
		
		if(in_array($columnObject->getType(), array("number", "price"))){
			$classes[] = "align-right";
		}
		
		if($columnObject->getIsEditable() || $columnObject->getIsEditableInline()){
			$classes[] = "editable";
		}
		
		if($columnObject->getType() == "price"){
			$out['currencyCode'] = $columnObject->getCurrencyCode();
		}
		
		if($columnObject->getFilter()!==false){
			$filterHeaderOptions = array();
			// Filter content
			if($columnObject->getOptions()){
				$filterHeaderOptions = array(
					"options"		=>	$columnObject->getOptions(),
					"allowEmpty"	=>	true
				);
			}
			$classes[] = "filterable";
			
			// Filter column
			$out["children"] = array(
				array(
					"renderHeaderCell"  => array(
						$columnObject->getType(), 
						$columnObject->getIndex(), 
						$filterHeaderOptions
					),
					"filterable"		=> 1,
					"sortable"			=> false,
					"options"			=> $columnObject->getOptions(),
					"field"				=> $columnObject->getIndex(),
					"className"			=> implode(" ", $classes)
				)
			);
			
		}
		
		if($columnObject->getIndex()=="name"){
			// Also add status
			$out['statusOptions'] = $this->getGridModel()->optionsToHash(
				$this->getGridModel()->
					getAttribute("status")->
					getSource()->
					getAllOptions()
			);
		}
		
		return $out;
	}
	
}
