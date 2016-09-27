<?php

class Snowdog_Freshmail_Block_Adminhtml_Request_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Init grid
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('request_grid');
        $this->setDefaultSort('request_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Init grid collection
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('snowfreshmail/api_request')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn('request_id', array(
            'header' => Mage::helper('snowfreshmail')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'request_id',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('snowfreshmail')->__('Created At'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'datetime',
            'index' => 'created_at',
        ));

        $this->addColumn('date_expires', array(
            'header' => Mage::helper('snowfreshmail')->__('Expires At'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'datetime',
            'index' => 'date_expires',
        ));

        $this->addColumn('processed_at', array(
            'header' => Mage::helper('snowfreshmail')->__('Processed At'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'datetime',
            'index' => 'processed_at',
            'default' => '----',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('snowfreshmail')->__('Status'),
            'width' => '100px',
            'renderer' => 'snowfreshmail/adminhtml_request_grid_renderer_status',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::helper('snowfreshmail')->getItemStatusesArray(),
        ));

        return parent::_prepareColumns();
    }

    /**
     * Return row url
     *
     * @param Varien_Object
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/details', array('request_id' => $row->getId()));
    }
}
