<?php

class Zolago_DropshipVendorAskQuestion_Block_Vendor_Question_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagopos_pos_grid');
        $this->setDefaultSort('question_date');
        $this->setDefaultDir('desc');
		$this->setSaveParametersInSession(1);
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }
	
	protected function _prepareCollection(){
		$collection = Mage::getModel('udqa/question')->getCollection();
		/* @var $collection ZolagoOs_OmniChannelVendorAskQuestion_Model_Mysql4_Question_Collection */
		$collection->
				joinProducts()->
				joinVendors();
				
		$collection->getSelect()->joinLeft(array(
		    'udropship_po' => $collection->getTable('udpo/po')),
		    'main_table.po_id = udropship_po.entity_id',
		    array(
		        'increment_id' => 'udropship_po.increment_id',
		        
            )
        )->joinLeft(array(
                'core_store' => $collection->getTable('core/store')),
                'main_table.store_id = core_store.store_id',
                array (
                    'store_name' => 'core_store.name',
                )
	    );   
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		/* @var $vendor Zolago_Dropship_Model_Vendor */
		$vendorsIds = $vendor->getChildVendorIds();
		$vendorsIds[] = $vendor->getId();
		
		$collection->addFieldToFilter("main_table.vendor_id", 
			array("in"=>  array_unique($vendorsIds)));
		
		$collection->addExpressionFieldToSelect('is_replied', 
				'IF(LENGTH(answer_text)>0,1,0)', 'is_replied');
		$collection->addExpressionFieldToSelect('answer_date', 
				'IF(main_table.answer_date>0,main_table.answer_date, NULL)', 'answer_date');


        $this->setCollection($collection);
        return parent::_prepareCollection();
	}

    /**
     * @param $collection ZolagoOs_OmniChannelVendorAskQuestion_Model_Mysql4_Question_Collection
     * @param $column Mage_Adminhtml_Block_Widget_Grid_Column
     * @return $this
     */
    protected function filterByEmailIfValid($collection, $column) {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
        $cond = $column->getFilter()->getCondition();

        if (Zend_Validate::is($value, 'EmailAddress')) {
            // overridden behavior
            $field = 'main_table.customer_email';
        }

        if ($field && isset($cond)) {
            $collection->addFieldToFilter($field, $cond);
        }

        return $this;
    }


    protected function _addColumnFilterToCollection($column){
		if($column->getIndex()=="is_replied"){
			$this->getCollection()->getSelect()->
				where("IF(LENGTH(answer_text)>0,1,0)=?", $column->getFilter()->getValue());
			return $this;
		}
		return parent::_addColumnFilterToCollection($column);
	}

	protected function _prepareColumns() {
		$_helper = Mage::helper("udqa");
		
		/*    <th><?php echo $this->__('Details')?></th>
            <th><?php echo $this->__('Is Replied')?></th>
            <th><?php echo $this->__('Customer Name')?></th>
            <th><?php echo $this->__('Question')?></th>
            <th><?php echo $this->__('Answer')?></th>
		*/
		
		$this->addColumn("store_id", array(
			"type"		=>	"text",
			"index"		=>	"store_name",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Store"),
		));
		$this->addColumn("product_name", array(
			"type"		=>	"text",
			"index"		=>	"product_name",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Product"),
		));
		$this->addColumn("product_sku", array(
			"type"		=>	"text",
			"index"		=>	"product_sku",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("SKU"),
		));

		$this->addColumn("order_id", array(
			"type"		=>	"text",
			"index"		=>	"increment_id",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Order ID"),
		));

		
		$this->addColumn("question_date", array(
			"type"		=>	"date",
			"index"		=>	"question_date",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Question date"),
		));
		
		$this->addColumn("answer_date", array(
			"type"		=>	"date",
			"index"		=>	"answer_date",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Answer date"),
		));
		
		$this->addColumn("customer_name", array(
			"type"		=>	"text",
			"index"		=>	"customer_name",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Customer name"),
            'filter_condition_callback' => array($this, 'filterByEmailIfValid'),
		));
		
		
		$this->addColumn("is_replied", array(
			"type"		=>	"options",
			"options"	=> array(
				0 => $this->__("No"),
				1 => $this->__("Yes")
			),
			"index"		=>	"is_replied",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Is Replied"),
		));
		
		
//		$this->addColumn("question_text", array(
//			"type"		=>	"text",
//			"index"		=>	"question_text",
//			"class"		=>  "form-controll",
//			"header"	=>	$_helper->__("Question"),
//		));
//		
//		$this->addColumn("answer_text", array(
//			"type"		=>	"text",
//			"index"		=>	"answer_text",
//			"class"		=>  "form-controll",
//			"header"	=>	$_helper->__("Answer"),
//		));
		

		
		$this->addColumn("actions", array(
                'header'    => $_helper->__('Action'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'question_id',
				'link_action'=> "*/*/questionEdit",
				'link_param'=> 'id',
				'link_label'=> $_helper->__('View'),
				'link_target'=>'_self',
                'filter'    => false,
                'sortable'  => false
        ));
		
		
		return parent::_prepareColumns();
	}


    public function getRowUrl($row){
        return $this->getUrl('*/*/questionEdit', array('id'=>$row->getId()));
    }

}