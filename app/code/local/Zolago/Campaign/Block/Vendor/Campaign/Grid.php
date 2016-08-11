<?php

class Zolago_Campaign_Block_Vendor_Campaign_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
     public function __construct() {
        parent::__construct();
        $this->setId('zolagocampaign_campaign_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
		// Need
        $this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }

	protected function _prepareCollection(){
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		/* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
		$collection = Mage::getResourceModel("zolagocampaign/campaign_collection");
        $collection->getSelect()
            ->join(
                array('campaign_website' => Mage::getSingleton('core/resource')->getTableName(
                    "zolagocampaign/campaign_website"
                )),
                'campaign_website.campaign_id = main_table.campaign_id',
                array("website_id" => "campaign_website.website_id")
            );
		$collection->addVendorFilter($vendor);

		$this->setCollection($collection);

        return parent::_prepareCollection();
	}
	
	protected function _prepareColumns() {
		$_helper = Mage::helper("zolagocampaign");
		$this->addColumn("name", array(
			"type"		=>	"text",
			"index"		=>	"name",
			"class"		=>  "form-control",
			"header"	=>	$_helper->__("Campaign"),
		));


        $websiteOptions =  Mage::getModel("zolagocampaign/source_campaign")
            ->getCampaignWebsites();

        $this->addColumn("website_id", array(
            "type"		=>	"options",
            'options'   => $websiteOptions,
            "index"		=>	"website_id",
            "class"		=>  "form-control",
            "header"	=>	$_helper->__('Websites'),
        ));

        if (Mage::helper("zolagodropship")->isLocalVendor()) {
            $this->addColumn("is_landing_page", array(
                "type" => "options",
                'options' => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                "index" => "is_landing_page",
                "class" => "form-control",
                "header" => $_helper->__("Landing page"),
            ));
        }

		
		$this->addColumn("type", array(
            "type"		=>	"options",
            "options"	=> Mage::getSingleton('zolagocampaign/campaign_type')->toOptionHash(),
			"index"		=>	"type",
			"class"		=>  "form-control",
			"header"	=>	$_helper->__("Campaign Type"),
		));
        $this->addColumn("date_from", array(
            "type"		=>	"date",
            "index"		=>	"date_from",
            "class"		=>  "form-control",
            "header"	=>	$_helper->__("Date From"),
        ));
        $this->addColumn("date_to", array(
            "type"		=>	"date",
            "index"		=>	"date_to",
            "class"		=>  "form-control",
            "header"	=>	$_helper->__("Date To"),
        ));
        $this->addColumn("price_source_id", array(
            "type"		=>	"options",
            "options"	=>  Mage::getSingleton('zolagocampaign/campaign_pricesource')->toOptionHash(),
            "index"		=>	"price_source_id",
            "class"		=>  "form-control",
            "header"	=>	$_helper->__("Price source"),
        ));
        $this->addColumn("percent", array(
            "type"		=>	"text",
            "index"		=>	"percent",
            "class"		=>  "form-control",
            "header"	=>	$_helper->__("Discount percent"),
        ));
        $this->addColumn("strikeout_type", array(
            "type"		=>	"options",
            "options"   =>  Mage::getSingleton('zolagocampaign/campaign_strikeout')->toOptionHash(),
            "index"		=>	"strikeout_type",
            "class"		=>  "form-control",
            "header"	=>	$_helper->__('Strikeout price'),
        ));

		$this->addColumn("status", array(
            "type"		=>	"options",
            "options"	=>  Mage::getSingleton('zolagocampaign/campaign_status')->toOptionHash(),
			"index"		=>	"status",
			"class"		=>  "form-control",
			"header"	=>	$_helper->__("Status"),
		));

		
		$this->addColumn("actions", array(
                'header'    => $_helper->__('Action'),
				'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
                'width'     => '50px',
                'type'      => 'action',
				'index'		=> 'campaign_id',
				'link_action'=> "*/*/edit",
				'link_param'=> 'id',
				'link_label'=> $_helper->__('Edit'),
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