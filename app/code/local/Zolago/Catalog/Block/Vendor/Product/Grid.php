<?php

class Zolago_Catalog_Block_Vendor_Product_Grid extends Mage_Core_Block_Template {

	const THUMB_WIDTH = 60;
	const THUMB_HEIGHT = 60;

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
	 * @param Varien_Object $columnObject
	 * @return array
	 */
	protected function mapColumn(Varien_Object $columnObject) {
		$attribute = null;
		
		
		if($columnObject->getAttribute()){
			$attribute = $columnObject->getAttribute();
		}
		$classes = array(
			"type-" . $columnObject->getType()
		);
		
		$headerClass = array(
			"header"
		);
		
		if($columnObject->getRequired()){
			$classes[] = "field-required";
			$headerClass[] = "field-required";
		}
		
		$out = array(
			"label" => $columnObject->getHeader(),
			"required" => $columnObject->getRequired(),
			"field" => $columnObject->getIndex(),
			"type" => $columnObject->getType(), 
			"fixed" => $columnObject->getFixed(),
			"sortable" => $columnObject->getSortable(),
			"className" => implode(" ", $headerClass)
		);
		
		
		if($columnObject->getEditable() || $columnObject->getEditableInline()){
			$classes[] = "editable";
		}
		
		if($columnObject->getType() == "price"){
			$out['currencyCode'] = $columnObject->getCurrencyCode();
		}
		
		if($columnObject->getFilter()!==false){
			
			// Set value 
			$filterHeaderOptions = array(
				"valueType"		=> $columnObject->getType()
			);
			
			// Filter content
			if($columnObject->getOptions()){
				$filterHeaderOptions['options'] = $columnObject->getOptions();
				$filterHeaderOptions['allowEmpty'] = true;
			}
			
			$classes[] = "filterable";
			
			// Filter column
			$out["children"] = array(
				array(
					"renderHeaderCell"  => array(
						$this->_getFilterType($columnObject), 
						$this->_getFilterIndex($columnObject), 
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
		
		switch ($columnObject->getIndex()) {
			case "status":
				$out['label'] = $this->__("St.");
			break;
		}
		
		
		return $out;
	}
	
	/**
	 * Map filter for index
	 * @param Varien_Object $column
	 * @return string
	 */
	protected function _getFilterIndex(Varien_Object $column) {
		if($column->getIndex()=="thumbnail"){
			return "images_count";
		}
		return $column->getIndex();
	}
	
	/**
	 * Map filter for filter
	 * @param Varien_Object $column
	 * @return string
	 */
	protected function _getFilterType(Varien_Object $column) {
		if($column->getType()=="image"){
			return "number";
		}
		return $column->getType();
	}
	
	/**
	 * @return int
	 */
	public function getThumbWidth() {
		return self::THUMB_WIDTH;
	}
	
	/**
	 * @return int
	 */
	public function getThumbHeight() {
		return self::THUMB_HEIGHT;
	}
	
}
