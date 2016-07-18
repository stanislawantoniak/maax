<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Adminhtml_Review_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('reviwGrid');
        $this->setDefaultSort('created_at');
    }

    protected function _prepareCollection()
    {
        $model = Mage::getModel('review/review');
        $collection = Mage::getResourceModel('udratings/review_shipment_collection');
        $collection->joinReviews()->joinShipmentItemData();

        if ($this->getVendorId() || $this->getRequest()->getParam('vendorId', false)) {
            $this->setVendorId(($this->getVendorId() ? $this->getVendorId() : $this->getRequest()->getParam('vendorId')));
            $collection->addEntityFilter($this->getVendorId());
        }

        if ($this->getCustomerId() || $this->getRequest()->getParam('customerId', false)) {
            $this->setCustomerId(($this->getCustomerId() ? $this->getCustomerId() : $this->getRequest()->getParam('customerId')));
            $collection->addCustomerFilter($this->getCustomerId());
        }

        if (Mage::registry('usePendingFilter') === true) {
            $collection->addStatusFilter($model->getPendingStatus());
        }

        $collection->addStoreData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $statuses = Mage::getModel('review/review')
            ->getStatusCollection()
            ->load()
            ->toOptionArray();

        foreach( $statuses as $key => $status ) {
            $tmpArr[$status['value']] = $status['label'];
        }

        $prefix = $this->uIsMassactionAvailable() ? '' : 'udratings_grid_';

        $statuses = $tmpArr;

        $this->addColumn($prefix.'review_id', array(
            'header'        => Mage::helper('review')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'rt.review_id',
            'index'         => 'review_id',
        ));

        $this->addColumn($prefix.'created_at', array(
            'header'        => Mage::helper('review')->__('Created On'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'filter_index'  => 'rt.created_at',
            'index'         => 'created_at',
        ));

        if( !Mage::registry('usePendingFilter') ) {
            $this->addColumn($prefix.'status', array(
                'header'        => Mage::helper('review')->__('Status'),
                'align'         => 'left',
                'type'          => 'options',
                'options'       => $statuses,
                'width'         => '100px',
                'filter_index'  => 'rt.status_id',
                'index'         => 'status_id',
            ));
        }

        $this->addColumn($prefix.'title', array(
            'header'        => Mage::helper('review')->__('Title'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.title',
            'index'         => 'title',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        if (!$this->getCustomerId()) {
        $this->addColumn($prefix.'nickname', array(
            'header'        => Mage::helper('review')->__('Nickname'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.nickname',
            'index'         => 'nickname',
            'format'        => sprintf('<a href="%sid/$customer_id/">$nickname</a>', $this->getUrl('adminhtml/customer/edit'))
        ));
        }

        $this->addColumn($prefix.'detail', array(
            'header'        => Mage::helper('review')->__('Review'),
            'align'         => 'left',
            'index'         => 'detail',
            'filter_index'  => 'rdt.detail',
            'type'          => 'text',
            'truncate'      => 50,
            'nl2br'         => true,
            'escape'        => true,
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn($prefix.'visible_in', array(
                'header'    => Mage::helper('review')->__('Visible In'),
                'index'     => 'stores',
                'type'      => 'store',
                'store_view' => true,
            ));
        }

        if (!$this->getVendorId()) {
        $this->addColumn($prefix.'udropship_vendor', array(
            'header'        => Mage::helper('review')->__('Vendor'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'udropship_vendor',
            'format'        => sprintf('<a onclick="this.target=\'blank\'" href="%sid/$udropship_vendor/">$vendor_name</a>', $this->getUrl('zolagoosadmin/adminhtml_vendor/edit'))
        ));
        }

        $this->addColumn($prefix.'increment_id', array(
            'header'        => Mage::helper('review')->__('Shipment'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'increment_id',
            'format'        => sprintf('<a onclick="this.target=\'blank\'" href="%sshipment_id/$entity_id/">$increment_id</a>', $this->getUrl('adminhtml/sales_shipment/view'))
        ));

        $this->addColumn($prefix.'product_name_list', array(
            'header'    => Mage::helper('review')->__('Product Name'),
            'align'     =>'left',
            'type'      => 'text',
            'index'     => 'product_name_list',
            'nl2br'     => true,
            'escape'    => true
        ));

        $this->addColumn($prefix.'product_sku_list', array(
            'header'    => Mage::helper('review')->__('Product SKU'),
            'align'     => 'right',
            'type'      => 'text',
            'width'     => '50px',
            'index'     => 'product_sku_list',
            'nl2br'     => true,
            'escape'    => true
        ));

        $this->addColumn($prefix.'action',
            array(
                'header'    => Mage::helper('adminhtml')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getReviewId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('adminhtml')->__('Edit'),
                        'url'     => array(
                            'base'=>'zosratingsadmin/review/edit',
                            'params'=> array(
                                'vendorId' => $this->getVendorId(),
                                'customerId' => $this->getCustomerId(),
                                'ret'       => ( Mage::registry('usePendingFilter') ) ? 'pending' : null
                            )
                         ),
                         'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));

        return parent::_prepareColumns();
    }

    protected $_uIsMassactionAvailable = true;
    public function uIsMassactionAvailable($flag=null)
    {
        $result = $this->_uIsMassactionAvailable;
        if (null !== $flag) {
            $this->_uIsMassactionAvailable = $flag;
        }
        return $result;
    }
    public function setUisMassactionAvailable($flag)
    {
        $this->uIsMassactionAvailable($flag);
        return $this;
    }
    protected function _prepareMassaction()
    {
        if ($this->uIsMassactionAvailable()) {
            $this->setMassactionIdField('review_id');
            $this->setMassactionIdFieldOnlyIndexValue(true);
            $this->getMassactionBlock()->setFormFieldName('udratings');

            $this->getMassactionBlock()->addItem('delete', array(
                'label'=> Mage::helper('review')->__('Delete'),
                'url'  => $this->getUrl('zosratingsadmin/review/massDelete', array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')),
                'confirm' => Mage::helper('review')->__('Are you sure?')
            ));

            $statuses = Mage::getModel('review/review')
                ->getStatusCollection()
                ->load()
                ->toOptionArray();
            array_unshift($statuses, array('label'=>'', 'value'=>''));
            $this->getMassactionBlock()->addItem('update_status', array(
                'label'         => Mage::helper('review')->__('Update Status'),
                'url'           => $this->getUrl('zosratingsadmin/review/massUpdateStatus', array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')),
                'additional'    => array(
                    'status'    => array(
                        'name'      => 'status',
                        'type'      => 'select',
                        'class'     => 'required-entry',
                        'label'     => Mage::helper('review')->__('Status'),
                        'values'    => $statuses
                    )
                )
            ));
        }
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('zosratingsadmin/review/edit', array(
            'id' => $row->getReviewId(),
            'vendorId' => $this->getVendorId(),
            'customerId' => $this->getCustomerId(),
            'ret'       => ( Mage::registry('usePendingFilter') ) ? 'pending' : null,
        ));
    }

    public function getGridUrl()
    {
        if( $this->getVendorId() || $this->getCustomerId() ) {
            return $this->getUrl('zosratingsadmin/review/' . (Mage::registry('usePendingFilter') ? 'pending' : ''), array(
                'vendorId' => $this->getVendorId(),
                'customerId' => $this->getCustomerId(),
            ));
        } else {
            return $this->getCurrentUrl();
        }
    }
}
