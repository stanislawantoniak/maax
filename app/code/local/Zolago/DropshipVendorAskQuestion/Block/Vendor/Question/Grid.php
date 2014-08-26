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
		$collection->
				joinProducts()->
				joinVendors()->
				joinShipments();
		
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		/* @var $vendor Zolago_Dropship_Model_Vendor */
		$vendorsIds = $vendor->getChildVendorIds();
		$vendorsIds[] = $vendor->getId();
		
		$collection->addFieldToFilter("main_table.vendor_id", 
			array("in"=>  array_unique($vendorsIds)));
		
		$collection->getSelect()->columns(array(
			'is_replied' => new Zend_Db_Expr('if(LENGTH(answer_text)>0,1,0)'),
			'answer_date' => new Zend_Db_Expr('if(main_table.answer_date>0, main_table.answer_date, NULL)')
		));

        $this->setCollection($collection);
        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$_helper = Mage::helper("udqa");
		
		/*    <th><?php echo $this->__('Details')?></th>
            <th><?php echo $this->__('Is Replied')?></th>
            <th><?php echo $this->__('Customer Name')?></th>
            <th><?php echo $this->__('Question')?></th>
            <th><?php echo $this->__('Answer')?></th>
		*/
		
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
		));
		
		$this->addColumn("visibility", array(
			"type"		=>	"options",
			"options"	=> array(
				0 => $this->__("Private"),
				1 => $this->__("Public")
			),
			"index"		=>	"visibility",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Visibility"),
		));
		
		
		$this->addColumn("order_increment_id", array(
			"type"		=>	"text",
			"index"		=>	"order_increment_id",
			"class"		=>  "form-controll",
			"header"	=>	$_helper->__("Order"),
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
                'header'    => Mage::helper('zolagopos')->__('Action'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'question_id',
				'link_action'=> "*/*/questionEdit",
				'link_param'=> 'id',
				'link_label'=> $this->__('View'),
				'link_target'=>'_self',
                'filter'    => false,
                'sortable'  => false
        ));
		
		
		return parent::_prepareColumns();
	}
	
	
	public function getRowUrl($item) {
		return null;
	}
	
}