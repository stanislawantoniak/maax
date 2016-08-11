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
            if (!in_array($columnObject->getIndex(), array("name", "status", "thumbnail"))) {
                $headerClass[] = "field-required-highlight";
            }
		}
		
		$out = array(
			"label"     => $columnObject->getHeader(),
			"required"  => $columnObject->getRequired(),
			"field"     => $columnObject->getIndex(),
			"type"      => $columnObject->getType(),
			"fixed"     => $columnObject->getFixed(),
			"sortable"  => $columnObject->getSortable(),
			"className" => implode(" ", $headerClass),
            "title"     => $columnObject->getHeader() . ($columnObject->getRequired() ? " *" : "")
		);
		
		
		if($columnObject->getEditable() || $columnObject->getEditableInline()){
			$classes[] = "editable";
		}
		
		if($columnObject->getType() == "price"){
			$out['currencyCode'] = $columnObject->getCurrencyCode();
		}
        // Force decode htmlspecialchars input value in grid editor
        /** @see skin/frontend/default/udropship/dojo/vendor-0.4/grid/PopupEditor.js::open() */
        if ($columnObject->getHtmlspecialcharsDecode()) {
            $out['htmlspecialcharsDecode'] = true;
        }
		
		if($columnObject->getFilter()!==false){
			
			// Set value 
			$filterHeaderOptions = array(
				"valueType"		=> $columnObject->getType(),
			);
			
			// Renderer opts - assoc
			if($columnObject->getOptions()){
				$filterHeaderOptions['options'] = $columnObject->getOptions();
				$filterHeaderOptions['filterOptions'] = $columnObject->getFilterOptions();
				$filterHeaderOptions['allowEmpty'] = $columnObject->hasAllowEmpty() ? $columnObject->getAllowEmpty() : 1;
			}

			
			$classes[] = "filterable";
			
			// Filter column
			$out["children"] = array(
				array(
                    /** renderHeaderCell passed as args called by js in: */
                    /** @see skin/frontend/default/udropship/dojo/vendor-0.3/grid/filter.js */
                    /** @see skin/frontend/default/udropship/dojo/vendor-0.4/grid/filter.js */
					"renderHeaderCell"  => array(
						$this->_getFilterType($columnObject),   // type
						$this->_getFilterIndex($columnObject),  // name
						$filterHeaderOptions                    // config
					),
					"filterable"		=> 1,
					"sortable"			=> false,
					"options"			=> $columnObject->getOptions(),
					"editOptions"		=> $columnObject->getEditOptions(),
					"field"				=> $columnObject->getIndex(),
					"className"			=> implode(" ", $classes)
				)
			);
			
		}
		
		switch ($columnObject->getIndex()) {
			case "status":
				$out['label'] = Mage::helper("zolagocatalog")->__("St.");
				$out['title'] = Mage::helper("zolagocatalog")->__("Product status") . ($columnObject->getRequired() ? " *" : "");
			break;
			case "thumbnail":
				$out['label'] = Mage::helper("zolagocatalog")->__("Img.");
                $out['title'] = Mage::helper("zolagocatalog")->__("Images") . ($columnObject->getRequired() ? " *" : "");
			break;
            case "description_status":
                $out['label'] = Mage::helper("zolagocatalog")->__("Description status");
                $out['title'] = Mage::helper("zolagocatalog")->__("Description status") . ($columnObject->getRequired() ? " *" : "");
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
