<?php

class Zolago_Campaign_Block_Vendor_Campaign_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
         parent::__construct();
         $this->setId('zolagocampaign_campaign_product_grid');
         $this->setDefaultSort('entity_id');
         $this->setDefaultDir('desc');
         // Need
         $this->setGridClass('z-grid');
         $this->setTemplate("zolagoadminhtml/widget/grid.phtml");
         //$this->setSaveParametersInSession(true);
         //$this->setUseAjax(true);
    }

	protected function _prepareCollection(){
        $campaignId = $this->getRequest()->getParam("id");

		$collection = Mage::getResourceModel("catalog/product_collection")
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
        ;
        $collection->getSelect()->join( array('zolago_campaign_product'=> 'zolago_campaign_product'),
            'zolago_campaign_product.product_id = e.entity_id')
        ->where("zolago_campaign_product.campaign_id=?",$campaignId);

        $collection->setPageSize(10);

		$this->setCollection($collection);

        return parent::_prepareCollection();
	}

    public function getMainButtonsHtml(){

    }
    protected function _prepareColumns()
    {
        $_helper = Mage::helper("zolagocampaign");

        $this->addColumn('sku',
            array(
                'header' => $_helper->__('Sku'),
                'index' => 'sku',
                "class"		=>  "form-controll",
                'width' => '50px',
            ));
        $this->addColumn('name',
            array(
                'header' => $_helper->__('Name'),
                'index' => 'name',
                "class"		=>  "form-controll",
                'width' => '50px',
            ));
        $this->addColumn('price',
            array(
                'header' => $_helper->__('Price'),
                'width' => '50px',
                'type' => 'number',
                "class"		=>  "form-controll",
                'index' => 'price',
            ));

        $this->addColumn('Remove',
            array(
                'header' => $_helper->__('Remove'),
                'renderer' => Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_removebutton"),
                'index' => 'product_id',
                'link_action' => "*/*/deleteProduct",
                'width' => '50px',
                'link_target' => '_self',
                'link_param' => 'id',
                'type' => 'action',
                'link_label' => 'Remove',
                'filter' => false,
                'sortable' => false
            ));
        return parent::_prepareColumns();
    }
	
	
	public function getRowUrl($item) {
		return null;
	}
	
}