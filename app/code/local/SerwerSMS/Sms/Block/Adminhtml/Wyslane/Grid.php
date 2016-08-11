<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Block_Adminhtml_Wyslane_Grid extends Mage_Adminhtml_Block_Widget_Grid{
    
    public function _construct(){
        
        parent::_construct();
        $this->setId('wyslaneGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection(){
        
        $collection = Mage::getModel('serwersms_model/SmsModel')->getCollection();
        $collection->addFieldToFilter('status', 'ok');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
        
        $this->addColumn('id',
                array(
                    'header' => 'ID',
                    'align' => 'right',
                    'width' => '50px',
                    'index' => 'id',
                ));
        $this->addColumn('data',
                array(
                    'header' => 'Data',
                    'align' => 'left',
                    'width' => '200px',
                    'index' => 'data',
                ));
        $this->addColumn('smsid',
                array(
                    'header' => 'SMS ID',
                    'align' => 'left',
                    'index' => 'smsid',
                ));
        $this->addColumn('numer',
                array(
                    'header' => 'Numer',
                    'align' => 'left',
                    'index' => 'numer',
                ));
        $this->addColumn('nadawca',
                array(
                    'header' => 'Nadawca',
                    'align' => 'left',
                    'index' => 'nadawca',
                ));
        $this->addColumn('typ',
                array(
                    'header' => 'Typ',
                    'align' => 'left',
                    'width' => '50px',
                    'index' => 'typ',
                ));
        $this->addColumn('raport',
                array(
                    'header' => 'Raport',
                    'align' => 'left',
                    'index' => 'raport',
                ));
        $this->addColumn('tresc',
                array(
                    'header' => 'Treść',
                    'align' => 'left',
                    'index' => 'tresc',
                ));
        return parent::_prepareColumns();
    }
    
//    public function getRowUrl($row){
//        return $this->getUrl('*/*/edit',array('id' => $row->getId()));
//    }
    
}

?>