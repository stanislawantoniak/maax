<?php

/**
 * Class Zolago_DropshipVendorAskQuestion_Block_Adminhtml_Question_Grid
 */
class Zolago_DropshipVendorAskQuestion_Block_Adminhtml_Question_Grid extends ZolagoOs_OmniChannelVendorAskQuestion_Block_Adminhtml_Question_Grid
{


    protected function _prepareColumns()
    {
        $statuses = Mage::getSingleton('udqa/source')
            ->setPath('statuses')
            ->toOptionHash();

        $prefix = $this->uIsMassactionAvailable() ? '' : 'udquestion_grid_';

        $this->addColumn($prefix . 'question_id', array(
            'header' => Mage::helper('udqa')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'question_id',
        ));

        $this->addColumn($prefix . 'question_date', array(
            'header' => Mage::helper('udqa')->__('Question Date'),
            'align' => 'left',
            'type' => 'datetime',
            'width' => '100px',
            'index' => 'question_date',
        ));

        $this->addColumn($prefix . 'question_status', array(
            'header' => Mage::helper('udqa')->__('Question Status'),
            'align' => 'left',
            'type' => 'options',
            'options' => $statuses,
            'width' => '100px',
            'index' => 'question_status',
        ));

        if (!$this->getCustomerId()) {
            $this->addColumn($prefix . 'customer_name', array(
                'header' => Mage::helper('udqa')->__('Customer Name'),
                'align' => 'left',
                'width' => '100px',
                'index' => 'customer_name',
                'format' => sprintf('<a href="%sid/$customer_id/">$customer_name</a>', $this->getUrl('adminhtml/customer/edit'))
            ));
        }

        $this->addColumn($prefix . 'question_text', array(
            'header' => Mage::helper('udqa')->__('Question Text'),
            'align' => 'left',
            'index' => 'question_text',
            'type' => 'text',
            'truncate' => 50,
            'nl2br' => true,
            'escape' => true,
        ));

        if (!$this->getVendorId()) {
            $this->removeColumn($prefix . 'vendor_id');
            $this->addColumn($prefix . 'vendor_id', array(
                'header' => Mage::helper('udqa')->__('Vendor'),
                'align' => 'left',
                'width' => '100px',
                'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
                'index' => 'vendor_id',
                'filter_index' => 'vendor.vendor_name',
                'filter' => 'udropship/vendor_gridColumnFilter',
                'format' => sprintf('<a onclick="this.target=\'blank\'" href="%sid/$vendor_id/">$vendor_name</a>', $this->getUrl('zolagoosadmin/adminhtml_vendor/edit'))
            ));
        }

        $this->addColumn($prefix . 'answer_date', array(
            'header' => Mage::helper('udqa')->__('Answer Date'),
            'align' => 'left',
            'type' => 'datetime',
            'width' => '100px',
            'index' => 'answer_date',
        ));

        $this->addColumn($prefix . 'answer_status', array(
            'header' => Mage::helper('udqa')->__('Answer Status'),
            'align' => 'left',
            'type' => 'options',
            'options' => $statuses,
            'width' => '100px',
            'index' => 'answer_status',
        ));

        $this->addColumn($prefix . 'answer_text', array(
            'header' => Mage::helper('udqa')->__('Answer Text'),
            'align' => 'left',
            'index' => 'answer_text',
            'type' => 'text',
            'truncate' => 50,
            'nl2br' => true,
            'escape' => true,
        ));

        $this->addColumn($prefix . 'context', array(
            'header' => Mage::helper('udqa')->__('Context'),
            'align' => 'left',
            'index' => 'increment_id',
            'renderer' => 'udqa/adminhtml_question_gridRenderer_context',
        ));

        $this->addColumn($prefix . 'action',
            array(
                'header' => Mage::helper('adminhtml')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('adminhtml')->__('Edit'),
                        'url' => array(
                            'base' => 'zosqaadmin/index/edit',
                            'params' => array(
                                'vendorId' => $this->getVendorId(),
                                'customerId' => $this->getCustomerId(),
                                'ret' => (Mage::registry('usePendingFilter')) ? 'pending' : null
                            )
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false
            ));

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }

}