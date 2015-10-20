<?php

class Gh_Regulation_Block_Adminhtml_Type_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('ghregulation_type_grid');
        $this->setDefaultSort('kind_name');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        /** @var GH_Regulation_Model_Resource_Regulation_Type_Collection $collection */
        $collection = Mage::getResourceModel('ghregulation/regulation_type_collection');
        $collection->joinKind();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {		
        $this->addColumn("name", array(
            "index"     =>"name",
            "header"    => Mage::helper("ghregulation")->__("Document type name"),
        ));
        $this->addColumn("kind", array(
            "index"     =>"kind_name",
            "header"    => Mage::helper("ghregulation")->__("Document kind name"),
            "type" 		=> "options",
            "options"	=> $this->_getKindOptions(),
        ));
        $this->addColumn('action', array(
            'header'    => Mage::helper('ghregulation')->__('Remove'),
            'width'     => '100px',
            'type'      => 'action',
            'getter'    => 'getRegulationTypeId',
            'actions' 	=> array(
                array(
                    'caption' => Mage::helper('ghregulation')->__('Remove'),
                    'url'	  => array('base' => '*/*/deleteType'),
                    'field'   => 'regulation_type_id',
                    'confirm' => Mage::helper('ghregulation')->__('Are you sure?'),
                ),
            ),
            'filter' => false,
            'sortable' => false,            
        ));
        return parent::_prepareColumns();
    }


    public function getRowUrl($row){
		if($row->getId()){
			return $this->getUrl('*/*/editType', array('regulation_type_id'=>$row->getId()));
		}
        return $this->getUrl('*/*/newType', array('back'=>'type'));
    }
    
    /**
     * array with document kinds - need to grid column
     */
     protected function _getKindOptions() {
         $collection = Mage::getResourceModel('ghregulation/regulation_kind_collection');
         $array = $collection->toArray();
         $out = array();
         if (!empty($array['items'])) {
             foreach ($array['items'] as $item) {
                 $out[$item['regulation_kind_id']] = $item['name'];
             }
         }
         return $out;
     }
    
}