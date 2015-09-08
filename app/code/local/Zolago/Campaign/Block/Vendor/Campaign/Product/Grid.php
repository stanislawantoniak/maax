<?php

class Zolago_Campaign_Block_Vendor_Campaign_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('zolagocampaign_campaign_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        // Need
        $this->setGridClass('z-grid');
        $this->setTemplate("zolagoadminhtml/widget/grid.phtml");

    }

    protected function _prepareCollection()
    {
        $campaignId = $this->getRequest()->getParam("id");

        $collection = Mage::getResourceModel("catalog/product_collection")
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('skuv')
            ->addAttributeToSelect('campaign_regular_id')
            ->addAttributeToSelect('campaign_info_id');
        $collection->getSelect()
            ->join(
                array('campaign_product' => Mage::getSingleton('core/resource')->getTableName(
                        "zolagocampaign/campaign_product"
                    )),
                'campaign_product.product_id = e.entity_id'
            )
            ->where("campaign_product.campaign_id=?", $campaignId);

        $collection->setPageSize(10);
        //$collection->printLogQuery(true);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getMainButtonsHtml()
    {
    }

    protected function _prepareColumns()
    {
        $_helper = Mage::helper("zolagocampaign");

        $this->addColumn(
            'skuv',
            array(
                 'header' => $_helper->__('Sku'),
                 'index'  => 'skuv',
                 "class"  => "form-control",
                 'width'  => '50px',
            )
        );
        $this->addColumn(
            'campaign_name',
            array(
                 'header' => $_helper->__('Name'),
                 'index'  => 'name',
                 "class"  => "form-control",
                 'width'  => '50px',
            )
        );
        $this->addColumn(
            'price',
            array(
                 'header' => $_helper->__('Price'),
                 'width'  => '50px',
                 'type'   => 'number',
                 "class"  => "form-control",
                 'index'  => 'price',
            )
        );
        $this->addColumn(
            'campaign_regular_id',
            array(
                'header' => $_helper->__('campaign_regular_id'),
                'width'  => '50px',
                'type'   => 'number',
                "class"  => "form-control",
                'column_css_class'=>'hidden',
                'header_css_class'=>'hidden' ,
                'index'  => 'campaign_regular_id',
            )
        );

        $this->addColumn(
            'Remove',
            array(
                 'header'      => $_helper->__('Remove'),
                 'renderer'    => Mage::getConfig()
                         ->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_removebutton"),
                 'index'       => 'product_id',
                 'link_action' => "*/*/removeProduct",
                 'width'       => '50px',
                 'link_target' => '_self',
                 'link_param'  => 'id',
                 'type'        => 'action',
                 'link_label'  => $_helper->__('Remove'),
                 'filter'      => false,
                 'sortable'    => false
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