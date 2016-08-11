<?php

class Zolago_Campaign_Block_Vendor_Campaign_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('zolagocampaign_campaign_banner_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        // Need
        $this->setGridClass('z-grid');
        $this->setTemplate("zolagocatalog/widget/grid.phtml");

    }

    protected function _prepareCollection()
    {
        $campaignId = $this->getRequest()->getParam("id");

        $collection = Mage::getResourceModel("zolagobanner/banner_collection");
        $collection->getSelect()
            ->join(
                array('banner_content' => Mage::getSingleton('core/resource')->getTableName(
                    "zolagobanner/banner_content"
                )),
                'banner_content.banner_id = main_table.banner_id'
            )
            ->where("campaign_id=?", $campaignId);

        $collection->setPageSize(10);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getMainButtonsHtml()
    {
    }

    public function getAddNewBannerPath()
    {
        $campaignId = $this->getRequest()->getParam("id");
        return Mage::getUrl('banner/vendor/new', array('campaign_id' => $campaignId, '_secure' => true));
    }

    protected function _prepareColumns()
    {
        $_helper = Mage::helper("zolagocampaign");

        $this->addColumn(
            'banner_name',
            array(
                'header' => $_helper->__('Name'),
                'index' => 'name',
                "class" => "form-control",
                'width' => '200px',
            )
        );
        $this->addColumn(
            'banner_type',
            array(
                'header' => $_helper->__('Type'),
                'width' => '50px',
                "type" => "options",
                "class" => "form-control",
                'index' => 'type',
                "options" => Mage::getSingleton('zolagobanner/banner_type')->toOptionHash(),
            )
        );
        $this->addColumn('action',
            array(
                'header' => Mage::helper('catalog')->__('Action'),
                'width' => '10px',
                'align' => 'right',
                'type' => 'action',
                'getter' => 'getId',

                'actions' => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url' => $this->getUrl('banner/vendor/edit', array('id' => '$banner_id', "_secure" => true))
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'banner_id',
            ));
        $this->addColumn(
            'Remove',
            array(
                'align' => 'right',
                'header' => $_helper->__('Remove'),
                'renderer' => Mage::getConfig()
                    ->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_removebutton"),
                'index' => 'banner_id',
                'link_action' => "*/*/removeBanner",
                'width' => '10px',
                'link_target' => '_self',
                'link_param' => 'id',
                'type' => 'action',
                'link_label' => $_helper->__('Remove'),
                'filter' => false,
                'sortable' => false
            )
        );
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/edit', array('_current' => true));
    }

    public function getRowUrl($item)
    {
        return null;
    }

}