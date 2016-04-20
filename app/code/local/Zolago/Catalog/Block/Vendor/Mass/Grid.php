<?php

class Zolago_Catalog_Block_Vendor_Mass_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	protected $_denyColumnList = null;
    protected $_useLazyLoad = false;
    protected $_isblockType = true;

    public function __construct() {
        parent::__construct();
        $this->setId('zolagocatalog_mass_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setGridClass('z-grid');

        $this->setSaveParametersInSession(false);
		$this->setTemplate("zolagocatalog/widget/gridblock.phtml");
		// Add custom renderes
		$this->setColumnRenderers(array(
			'multiselect'	=>	'zolagoadminhtml/widget_grid_column_renderer_multiselect',
			'image'			=>	'zolagoadminhtml/widget_grid_column_renderer_image',
			'status'		=>	'zolagoadminhtml/widget_grid_column_renderer_status',
			'link'			=>	'zolagoadminhtml/widget_grid_column_renderer_link'
		));
		$this->setColumnFilters(array(
			"multiselect"	=>	'zolagoadminhtml/widget_grid_column_filter_multiselect',
			'status'		=>	'adminhtml/widget_grid_column_filter_select',
		));
    }

    public function useLazyLoad(){
        $this->_useLazyLoad = true;
    }

    public function isLazyLoad(){
        return $this->_useLazyLoad;
    }

    public function isBlockType(){
        return $this->_isblockType;
    }

	protected function _setCollectionOrder($column) {
		$attribute = $column->getAttribute();
		if($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute && $this->isAttributeEnumerable($attribute)){
			$source = $column->getAttribute()->getSource();
			if($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Boolean){
				// Need fix
				Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addBoolValueSortToCollection(
						$attribute,
						$this->getCollection(),
						$column->getDir()
				);
				return $this;
			}elseif($attribute->getFrontendInput()=="multiselect"){
				// Need fix - comma
				Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addMultipleValueSortToCollection(
						$attribute,
						$this->getCollection(),
						$column->getDir()
				);
				return $this;
			}
		}
		return parent::_setCollectionOrder($column);
	}

	public function getPopupContent() {
		return $this->getChildHtml('popup_content');
	}
	public function getHideColumnsButtonHtml() {
		return $this->getChildHtml('hide_column_button');
	}
    public function getAttributeSetSwitcherHtml() {
        return $this->getChildHtml('attribute_set_switcher');
    }

    public function getColumnWidthStyle($column){

        $data = $column->getData();
        $default_width = 100;
        $width = null;

        if($data['index'] == 'entity_id'){
            $width = 40;
        }
        else{
            if(isset($data['width'])){
                $width = $data['width'];
            }
            if(isset($data['attribute'])){
                $width = $data['attribute']->getColumnWidth();
            }
        }

        if(!$width) $width = $default_width;

        return "style='width: " . $width . "px;'";
    }

	protected function _prepareLayout() {
        $ret = parent::_prepareLayout();
		$this->setChild('popup_content',
			$this->getLayout()->createBlock('zolagocatalog/vendor_mass_columnspopup')
				->setData(array(
					'parent' => $this,
					'denyList' => $this->_getDenyColumnList(),
				))
		);
        $this->setChild('hide_column_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('zolagocatalog')->__('Show/Hide columns'),
                    'onclick'   => 'javascript:openMyPopup()',
                    'class'   => 'task'
                ))
        );
        $this->setChild('attribute_set_switcher',
            $this->getLayout()->createBlock('zolagocatalog/vendor_mass_attributesetswitcher')
                ->setTemplate("zolagocatalog/widget/grid/attributesetswitcher.phtml")
                ->setData(array(
                    'parent' => $this
                ))
        );

        $this->unsetChild("reset_filter_button");
		return $ret;
	}
	protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        /* @var $collection Zolago_Catalog_Model_Resource_Product_Collection */

		$collection->setFlag("skip_price_data", true);

		// Set store id
		$store = $this->getStore();
		$collection->setStoreId($store->getId());

		if($store->getId()){
			$collection->addStoreFilter($store);
		}

		// Add non-grid filters
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendorId());
		$collection->addAttributeToFilter("attribute_set_id", $this->getAttributeSet()->getId());
		$collection->addAttributeToFilter("visibility", array("in"=>array(
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
		)));


		//Add Active Static Filters to Collection - Start
		$staticFilters		= $this->getStaticFilters();
		$staticFilterValues	= $this->getStaticFilterValues();
		if ($staticFilters && $staticFilterValues) {
			foreach ($staticFilters as $staticFilter) {
				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
				$attribute = Mage::getModel('eav/entity_attribute')->load($staticFilter);
				if ($attribute->getGridPermission() == Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER
					&& array_key_exists($staticFilter, $staticFilterValues)) {
					$collection->addAttributeToFilter($attribute->getAttributeCode(), $staticFilterValues[$staticFilter]);
				}
			}
		}
		//Add Active Static Filters to Collection - End


	    // Prepare collection data
		foreach($this->getColumns() as $key=>$column){
			$columnData = $column->getData();
			// Add regular dynamic attributes data
			if($this->_canShowColumn($key, $columnData)){
				if(isset($columnData['attribute']) &&
					$columnData['attribute'] instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
					// By regular attribute
					$collection->addAttributeToSelect($columnData['attribute']->getAttributeCode());
				}elseif($key=="images_count"){
					$collection->addImagesCount(false);
				}
			}
		}

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * list of not allowed columns (from session)
     */
    protected function _getDenyColumnList() {
    	if (is_null($this->_denyColumnList)) {
    		$attributeSet = $this->getAttributeSet()->getId();
    		$list = Mage::getSingleton('udropship/session')->getData('denyColumnList');
 			$out = array();
    		if ($list && isset($list[$attributeSet])) {
				$out = $list[$attributeSet];
			}
			$this->_denyColumnList = $out;
    	}
    	return $this->_denyColumnList;
    }
    public function getAllColumns() {
    	$columns = $this->_getAllPossibleColumns();
    	$out = array();
    	foreach ($columns as $key => $column) {
	    	$out[$key] = $this->_processColumnConfig($key, $column);
		}
		return $out;
    }
	/**
	 * @return array
	 */
	protected function _getAllPossibleColumns(){
		if(!$this->getData("all_possible_columns")){
			$columns = array();
			// Build final columns
			foreach($this->_prepareFixedStartColumns() as $key=>$column){
				$columns[$key] = $column;
			}
			foreach($this->_prepareDynamicColumns() as $key=>$column){
				$columns[$key] = $column;
			}
			foreach($this->_prepareFixedEndColumns() as $key=>$column){
				$columns[$key] = $column;
			}
			$this->setData("all_possible_columns", $columns);
		}
		return $this->getData("all_possible_columns");
	}


	/**
	 * Prepare layout for static start columns
	 * @return array
	 */
	protected function _prepareFixedStartColumns(){
		 $static = $this->_getFixedColumns();
		 if(isset($static['start'])){
			 return $static['start'];
		 }
		 return array();
	}

	/**
	 * Prepare layout for static end columns
	 * @return array Description
	 */
	protected function _prepareFixedEndColumns(){
		 $static = $this->_getFixedColumns();
		 $columns = array();
		 if(isset($static['end'])){
			 return $static['end'];
		 }
		 return array();
	}

	/**
	 * @return array()
	 */
	protected function	_getFixedColumns(){
		if(!$this->getData("fixed_columns")){

			$columnStart = array();

			// Thumb
			$thumbnail =  Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "thumbnail");
			$thumbnail->setStoreId($this->getLabelStore()->getId());
			$columnStart[$thumbnail->getAttributeCode()] = array(
				"index"		=> $thumbnail->getAttributeCode(),
				"type"		=> "image",
				"attribute" => $thumbnail,
				"clickable" => true,
				"header"	=> $this->_getColumnLabel($thumbnail),
				"width"		=> "80",
				"filter"	=> false,
				"sortable"	=> false
			);

			// Name
			$name = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "name");
			$name->setStoreId($this->getLabelStore()->getId());
			$columnStart[$name->getAttributeCode()] = array(
				"index"		=> $name->getAttributeCode(),
				"type"		=> "text",
				"width"		=> "200",
				"attribute" => $name,
				"clickable" => true,
				"header"	=> $this->_getColumnLabel($name),
			);


			// Status
			$status = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "status");
			$status->setStoreId($this->getLabelStore()->getId());

			$columnStart[$status->getAttributeCode()] = array(
				"index"		=> $status->getAttributeCode(),
				"type"		=>"options",
				"attribute" => $status,
				"clickable" => true,
				"width"		=> "30",
				"header"	=> $this->_getColumnLabel($status),
			);

			// Image count
			$columnStart["images_count"] = array(
				"index"		=> "images_count",
				"type"		=> "text",
				"header"	=> $this->__("Im."),
				"width"		=> "30",
				"filter"	=> false
			);

			// Sku
			// @todo get attribute by unirgy dropship config
			$sku = Mage::getModel("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, "sku");
			$sku->setStoreId($this->getLabelStore()->getId());
			$columnStart[$sku->getAttributeCode()] = array(
				"index"		=> $sku->getAttributeCode(),
				"type"		=>"text",
				"width"		=> "80",
				"clickable" => true,
				"header"	=> $this->_getColumnLabel($sku),
			);

			// End columns
			$columnEnd = array();

//			$columnEnd["edit"] = array(
//				"index"			=> "entity_id",
//				'type'			=> 'link',
//				'link_action'	=> 'udprod/vendor/productEdit',
//				'link_param'	=> 'id',
//				'link_label'	=> Mage::helper("zolagocatalog")->__("Edit form"),
//				"header"		=> Mage::helper("zolagocatalog")->__("Edit"),
//				"width"			=> "100",
//				"filter"		=> false,
//				"sortable"		=> false
//			);

			$this->setData("fixed_columns", array(
				"start" => $columnStart,
				"end" => $columnEnd
			));
		}
		return $this->getData("fixed_columns");
	}

	/**
	 * @return type
	 */
	protected function _prepareDynamicColumns(){
		$attributeCollection = $this->_getGridVisibleAttributes();
		$columns = array();
		foreach($attributeCollection as $attribute){
			/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
			$attribute->setStoreId($this->getLabelStore()->getId());
			$code = $attribute->getAttributeCode();
			$data = array(
				"index"     => $code,
				'type'		=> $this->_getColumnType($attribute),
				"header"    => $this->_getColumnLabel($attribute),
				"attribute"	=> $attribute,
			);
			$columns[$code] = $data;
		}
		return $columns;
	}


	protected function _prepareColumns() {
		foreach($this->_getAllPossibleColumns() as $key=>$column){
			if($this->_canShowColumn($key, $column)){
				$this->addColumn($key, $this->_processColumnConfig($key, $column));
			}
		}
		parent::_prepareColumns();
    }

	/**
	 * use this to checkou out witch column display
	 * @param type $key
	 * @param array $column
	 * @return boolean
	 */
	protected function _canShowColumn($key, array $column) {
		$deny = $this->_getDenyColumnList();
		return empty($deny[$key]);
	}


	protected function _processColumnConfig($key, array $config){

		$attribute = null;
		if(isset($config['attribute']) && $config['attribute'] instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
			$attribute = $config['attribute'];
		}
		/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */

		if($attribute){
			$extend = array();
			// Process select
			$frontendType = $attribute->getFrontendInput();
			if($this->isAttributeEnumerable($attribute)){
				if($frontendType=="multiselect"){
					$extend['type'] = "multiselect";
				}else{
					$extend['type'] = "options";
				}
				$extend['align'] = "center";
				if($attribute->getSource()){
					$extend['options']  = array();
					foreach($attribute->getSource()->getAllOptions(false) as $option){
						$extend['options'][$option['value']]=$option['label'];
					}
				}
				if($attribute->getAttributeCode()=="status"){
					$extend['type']="status";
					$extend['filter']=false;
					$extend['header']=$this->__("St.")." <span class=\"required\">*</span>";
				}
			}elseif($frontendType=="price"){
				$extend['type'] = "price";
				$extend['currency_code'] = $this->getStore()->getBaseCurrency()->getCode();
			}elseif($frontendType=="media_image"){
				$extend['type'] = "image";
				$extend['clickable'] = true;
				$extend['width'] = "100px";
				$extend['filter'] = false;
				$extend['sortable'] = false;
			}
			return array_merge($config, $extend);
		}

		return $config;
	}

	public function isAttributeEnumerable(Mage_Catalog_Model_Resource_Eav_Attribute $attribute){
		switch ($attribute->getFrontendInput()){
			case "select":
			case "multiselect":
			case "boolean":
				return true;
			break;
		}
		return false;
	}


	protected function _getColumnLabel(Mage_Catalog_Model_Resource_Eav_Attribute $attribute){
		 $label = $attribute->getStoreLabel($this->getLabelStore()->getId());
		 if($attribute->getIsRequired()){
			 $label .= " <span class=\"required\">*</span>";
		 }
		 return $label;
	}


	protected function _getColumnType(Mage_Catalog_Model_Resource_Eav_Attribute $attribute) {
		switch ($attribute->getBackendType()) {
			case "text":
			case "varchar":
				return "text";
			break;
			case "int":
			case "decimal":
				return "number";
			break;
			case "datetime":
				return "datetime";
			break;

		}
		return "text";
	}

	/**
	 * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 */
	public function getGridVisibleAttributes(){
		return $this->_getGridVisibleAttributes();
	}


	/**
	 * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 */
	protected function _getGridVisibleAttributes() {
		if(!$this->getData("grid_visible_attributes")){
			$collection = Mage::getResourceModel("catalog/product_attribute_collection");
			/* @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */

			Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addAttributeSetFilterAndSort(
					$collection,
					$this->getAttributeSet()
			);

            //$collection->addIsNotUniqueFilter();
			$collection->addFieldToFilter("grid_permission", array("in"=>array(
				Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::DISPLAY,
				Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION,
				Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::INLINE_EDITION,
			)));

			// Exclude static columns
			$static = $this->_getStaticAttributes();
			if(count($static)){
				$collection->addFieldToFilter("attribute_code", array("nin"=>$static));
			}

			$this->setData("grid_visible_attributes", $collection);
		}
		return $this->getData("grid_visible_attributes");
	}

	/**
	 * Get static columns
	 * @return array
	 */
	protected function _getStaticAttributes(){
		$static = array();
		foreach($this->_getFixedColumns() as $position){
			foreach($position as $column){
				$static[] = $column['index'];
			}
		}
		return $static;
	}

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

    public function getRowUrl($row){
        return null;
    }

	/**
	 * @return Zolago_Dropship_Model_Session
	 */
	protected function _getSession(){
		return Mage::getSingleton('udropship/session');
	}

	/**
	 * @return int
	 */
	public function getVendorId() {
		return $this->_getSession()->getVendorId();
	}

	/**
	 * @return ZolagoOs_OmniChannel_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
	}

	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Set
	 */
	public function getAttributeSet() {
		if($this->getParentBlock()){
			return $this->getParentBlock()->getCurrentAttributeSet();
		}
		return Mage::getModel("eav/entity_attribute_set")->load(
			Mage::app()->getRequest()->getParam("attribute_set")
		);
	}

	public function getStaticFilters() {
		if($this->getParentBlock()){
			return $this->getParentBlock()->getCurrentStaticFilters();
		}

		$staticFilters		= Mage::app()->getRequest()->getParam("staticFilters", 0);
		$staticFiltersIds	= false;

		for ($i = 1; $i <= $staticFilters; $i++) {
			if (Mage::app()->getRequest()->getParam("staticFilterId-".$i)) {
				$staticFiltersIds[] = Mage::app()->getRequest()->getParam("staticFilterId-".$i);
			}
		}

		return $staticFiltersIds;
	}

	/**
	 * @return Mage_Eav_Model_Entity_Attribute
	 */
	public function getStaticFilterValues() {
		if($this->getParentBlock()){
			return $this->getParentBlock()->getCurrentStaticFilterValues();
		}

		$staticFilters			= Mage::app()->getRequest()->getParam("staticFilters", 0);
		$staticFiltersValues	= false;

		for ($i = 1; $i <= $staticFilters; $i++) {
			if (Mage::app()->getRequest()->getParam("staticFilterId-".$i) && Mage::app()->getRequest()->getParam("staticFilterValue-".$i)) {
				$staticFiltersValues[Mage::app()->getRequest()->getParam("staticFilterId-".$i)] = Mage::app()->getRequest()->getParam("staticFilterValue-".$i);
			}
		}

		return $staticFiltersValues;
	}

	/**
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		if($this->getParentBlock()){
			return $this->getParentBlock()->getCurrentStore();
		}
		return Mage::app()->getStore(
			Mage::app()->getRequest()->getParam("store", 0)
		);
	}

	/**
	 * Returns store used form labels values
	 * @return Mage_Core_Model_Store
	 */
	public function getLabelStore(){
        /** @var Zolago_Catalog_Helper_Attribute $helper */
        $helper = Mage::helper("zolagocatalog/attribite");
        return $helper->getLabelStore($this->getVendor(), $this->getStore());
	}

	/**
	 * @param Varien_Object $row
	 * @return string|null
	 */
	public function getRowClass(Varien_Object $row) {
		return null;
	}


	public function getCellClass(Varien_Object $item, Mage_Adminhtml_Block_Widget_Grid_Column $column) {
		$classes = array();

        $data = $column->getData();
        if($data['index'] == 'thumbnail'){
            $classes[] = 'thumb';
        }
        elseif($data['index'] == 'name'){
            $classes[] = 'product-name';

            $status_column = $this->getColumn('status');
            if($status_column){
                $status_data = $item->getData($status_column->getIndex());
                switch($status_data){
                    case Mage_Catalog_Model_Product_Status::STATUS_ENABLED:
                        $classes[] = "status-enabled";
                        break;
                    case Mage_Catalog_Model_Product_Status::STATUS_DISABLED:
                        $classes[] = "status-disabled";
                        break;
                    default:
                        $classes[] = "status-other";
                        break;
                }
            }
        }
		if($column->getAttribute() instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
			if($column->getAttribute()->getIsRequired() && $data==""){
				$classes[] = "required-cell";
			}
		}
		if($classes){
			return implode(" ", $classes);
		}
		return null;
	}

    public function getHeaderHtml(Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        $data = $column->getData();
        switch($data['index']){

            case 'thumbnail':
                $column = $this->getColumn('images_count');
                break;
        }

        return $column->getHeaderHtml();
    }

    public function getAdditionalHTML(Varien_Object $item, Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        $html = '';
        $data = $column->getData();
        switch($data['index']){

            case 'thumbnail':
                $images_count_column = $this->getColumn('images_count');
                if($images_count_column){
                    $html .= "<span class='images-count'>" . $images_count_column->getRowField($item) . "</span>";
                }
                break;

            case 'name':
                $sku_column = $this->getColumn('sku');
                $status_column = $this->getColumn('status');
                if($sku_column){
                    $html .= "<div class='row meta'>";
                    $html .= "<div class='col-sm-8 sku'>(" . $sku_column->getRowField($item) . ")</div>";
                    $html .= "<div class='col-sm-4 status'>(" . $status_column->getRowField($item) . ")</div>";
                    $html .= "</div>";
                }
                break;
        }

        return $html;
    }

    public function isColumnVisible(Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        $show = true;
        $data = $column->getData();
        switch($data['index']){

            case 'sku':
            case 'status':
            case 'images_count':
                $show = false;
                break;
        }

        return $show;
    }

    public function getCollectionIdsString()
    {
        $gridIds = $this->getCollection()->getAllIds();

        if(!empty($gridIds)) {
            return join(",", $gridIds);
        }
        return '';
    }
}
