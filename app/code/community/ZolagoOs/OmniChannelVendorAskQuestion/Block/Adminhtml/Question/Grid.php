<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Adminhtml_Question_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('questionGrid');
        $this->setDefaultSort('created_at');
    }

    protected function _prepareCollection()
    {
        $model = Mage::getModel('udqa/question');
        $collection = Mage::getResourceModel('udqa/question_collection');
        $collection->joinShipments()->joinProducts()->joinVendors();

        if ($this->getVendorId() || $this->getRequest()->getParam('vendorId', false)) {
            $this->setVendorId(($this->getVendorId() ? $this->getVendorId() : $this->getRequest()->getParam('vendorId')));
            $collection->addVendorFilter($this->getVendorId());
        }

        if ($this->getCustomerId() || $this->getRequest()->getParam('customerId', false)) {
            $this->setCustomerId(($this->getCustomerId() ? $this->getCustomerId() : $this->getRequest()->getParam('customerId')));
            $collection->addCustomerFilter($this->getCustomerId());
        }

        if (Mage::registry('usePendingFilter') === true) {
            $collection->addPendingStatusFilter();
        }

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        $id = $column->getId();
        $value = $column->getFilter()->getValue();
        switch ($id) {
            case 'vendor_id':
                $this->getCollection()->addVendorFilter($value);
                return $this;
            case 'context':
                $this->getCollection()->addContextFilter($value);
                return $this;
        }
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    protected function _prepareColumns()
    {
        $statuses = Mage::getSingleton('udqa/source')
            ->setPath('statuses')
            ->toOptionHash();

        $prefix = $this->uIsMassactionAvailable() ? '' : 'udquestion_grid_';

        $this->addColumn($prefix.'question_id', array(
            'header'        => Mage::helper('udqa')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'question_id',
        ));

        $this->addColumn($prefix.'question_date', array(
            'header'        => Mage::helper('udqa')->__('Question Date'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'index'         => 'question_date',
        ));

        $this->addColumn($prefix.'question_status', array(
            'header'        => Mage::helper('udqa')->__('Question Status'),
            'align'         => 'left',
            'type'          => 'options',
            'options'       => $statuses,
            'width'         => '100px',
            'index'         => 'question_status',
        ));

        if (!$this->getCustomerId()) {
        $this->addColumn($prefix.'customer_name', array(
            'header'        => Mage::helper('udqa')->__('Customer Name'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'customer_name',
            'format'        => sprintf('<a href="%sid/$customer_id/">$customer_name</a>', $this->getUrl('adminhtml/customer/edit'))
        ));
        }

        $this->addColumn($prefix.'question_text', array(
            'header'        => Mage::helper('udqa')->__('Question Text'),
            'align'         => 'left',
            'index'         => 'question_text',
            'type'          => 'text',
            'truncate'      => 50,
            'nl2br'         => true,
            'escape'        => true,
        ));

        if (!$this->getVendorId()) {
        $this->addColumn($prefix.'vendor_id', array(
            'header'        => Mage::helper('udqa')->__('Vendor'),
            'align'         => 'left',
            'width'         => '100px',
            'options'       => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'index'         => 'vendor_id',
            'filter'        => 'udropship/vendor_gridColumnFilter',
            'format'        => sprintf('<a onclick="this.target=\'blank\'" href="%sid/$vendor_id/">$vendor_name</a>', $this->getUrl('zolagoosadmin/adminhtml_vendor/edit'))
        ));
        }

        $this->addColumn($prefix.'answer_date', array(
            'header'        => Mage::helper('udqa')->__('Answer Date'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'index'         => 'answer_date',
        ));

        $this->addColumn($prefix.'answer_status', array(
            'header'        => Mage::helper('udqa')->__('Answer Status'),
            'align'         => 'left',
            'type'          => 'options',
            'options'       => $statuses,
            'width'         => '100px',
            'index'         => 'answer_status',
        ));

        $this->addColumn($prefix.'answer_text', array(
            'header'        => Mage::helper('udqa')->__('Answer Text'),
            'align'         => 'left',
            'index'         => 'answer_text',
            'type'          => 'text',
            'truncate'      => 50,
            'nl2br'         => true,
            'escape'        => true,
        ));

        $this->addColumn($prefix.'context', array(
            'header'        => Mage::helper('udqa')->__('Context'),
            'align'         => 'left',
            'index'         => 'increment_id',
            'renderer'      => 'udqa/adminhtml_question_gridRenderer_context',
        ));

        $this->addColumn($prefix.'action',
            array(
                'header'    => Mage::helper('adminhtml')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('adminhtml')->__('Edit'),
                        'url'     => array(
                            'base'=>'zosqaadmin/index/edit',
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
            $this->setMassactionIdField('question_id');
            $this->setMassactionIdFieldOnlyIndexValue(true);
            $this->getMassactionBlock()->setFormFieldName('questions');

            $this->getMassactionBlock()->addItem('delete', array(
                'label'=> Mage::helper('udqa')->__('Delete'),
                'url'  => $this->getUrl('zosqaadmin/index/massDelete', array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')),
                'confirm' => Mage::helper('udqa')->__('Are you sure?')
            ));

            $statuses = Mage::getSingleton('udqa/source')
                ->setPath('statuses')
                ->toOptionArray();
            array_unshift($statuses, array('label'=>'', 'value'=>''));
            $this->getMassactionBlock()->addItem('update_question_status', array(
                'label'         => Mage::helper('udqa')->__('Update Question Status'),
                'url'           => $this->getUrl('zosqaadmin/index/massUpdateQuestionStatus', array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')),
                'additional'    => array(
                    'status'    => array(
                        'name'      => 'status',
                        'type'      => 'select',
                        'class'     => 'required-entry',
                        'label'     => Mage::helper('udqa')->__('Status'),
                        'values'    => $statuses
                    )
                )
            ));
            $this->getMassactionBlock()->addItem('update_answer_status', array(
                'label'         => Mage::helper('udqa')->__('Update Answer Status'),
                'url'           => $this->getUrl('zosqaadmin/index/massUpdateAnswerStatus', array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')),
                'additional'    => array(
                    'status'    => array(
                        'name'      => 'status',
                        'type'      => 'select',
                        'class'     => 'required-entry',
                        'label'     => Mage::helper('udqa')->__('Status'),
                        'values'    => $statuses
                    )
                )
            ));
            $this->getMassactionBlock()->addItem('send_customer_notification', array(
                'label'         => Mage::helper('udqa')->__('Send Customer Notification'),
                'url'           => $this->getUrl('zosqaadmin/index/massSendCustomer', array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')),
            ));
            $this->getMassactionBlock()->addItem('send_vendor_notification', array(
                'label'         => Mage::helper('udqa')->__('Send Vendor Notification'),
                'url'           => $this->getUrl('zosqaadmin/index/massSendVendor', array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')),
            ));
        }
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('zosqaadmin/index/edit', array(
            'id' => $row->getId(),
            'vendorId' => $this->getVendorId(),
            'customerId' => $this->getCustomerId(),
            'ret'       => ( Mage::registry('usePendingFilter') ) ? 'pending' : null,
        ));
    }

    public function getGridUrl()
    {
        if( $this->getVendorId() || $this->getCustomerId() ) {
            return $this->getUrl('zosqaadmin/index/' . (Mage::registry('usePendingFilter') ? 'pending' : ''), array(
                'vendorId' => $this->getVendorId(),
                'customerId' => $this->getCustomerId(),
            ));
        } else {
            return $this->getCurrentUrl();
        }
    }
}
