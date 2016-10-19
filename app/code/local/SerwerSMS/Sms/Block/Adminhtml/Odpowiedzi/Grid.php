<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Block_Adminhtml_Odpowiedzi_Grid extends Mage_Adminhtml_Block_Widget_Grid{
    
    public function _construct(){
        
        parent::_construct();
        $this->setId('odpowiedziGrid');
        $this->setDefaultSort('id_odp');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection(){
        
        $collection = Mage::getModel('serwersms_model/OdpowiedziModel')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
        
        $this->addColumn('id_odp',
                array(
                    'header' => 'ID',
                    'align' => 'right',
                    'width' => '50px',
                    'index' => 'id_odp',
                ));
        $this->addColumn('data',
                array(
                    'header' => 'Data',
                    'align' => 'left',
                    'width' => '200px',
                    'index' => 'data',
                ));
        $this->addColumn('numer',
                array(
                    'header' => 'Numer',
                    'align' => 'left',
                    'index' => 'numer',
                ));
        $this->addColumn('wiadomosc',
                array(
                    'header' => 'Wiadomość',
                    'align' => 'left',
                    'index' => 'wiadomosc',
                ));
        return parent::_prepareColumns();
    }
    
}

?>