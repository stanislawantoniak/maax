<?php
class Zolago_Rma_Block_Adminhtml_Rma_ReturnReason_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct(){
        parent::__construct();
           $this->setId('returnRasonsList');
           $this->setUseAjax(true);
           $this->setDefaultSort('created_at');
           $this->setFilterVisibility(true);
           $this->setPagerVisibility(true);
    }

    protected function _prepareCollection(){
           $collection = Mage::getModel('zolagorma/rma_reason')
                           ->getCollection();
           $this->setCollection($collection);

           return parent::_prepareCollection();
    }

    protected function _prepareColumns(){
		
       $this->addColumn('return_reason_id', array(
               'header'   => Mage::helper('zolagorma')->__('Id'),
               'width'    => 50,
               'index'    => 'return_reason_id',
               'sortable' => false,
           ));
       $this->addColumn('name', array(
            	'header'   => Mage::helper('zolagorma')->__('Name'),
               	'index'    => 'name',
               	'sortable' => true,
        ));
       $this->addColumn('auto_days', array(
               'header'   => Mage::helper('zolagorma')->__('Instant return days #'),
               'width'    => 100,
               'index'    => 'auto_days',
               'sortable' => true,
           ));
        $this->addColumn('allowed_days', array(
               'header'   => Mage::helper('zolagorma')->__('Acknowledged return days #'),
               'width'    => 100,
               'index'    => 'allowed_days',
               'sortable' => true,
           ));
	   $this->addColumn('message', array(
               'header'   => Mage::helper('zolagorma')->__('Message'),
               'index'    => 'message',
               'sortable' => false,
           ));

       return parent::_prepareColumns();
   }

    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array('return_reason_id'=>$row->getId()));
    }
}