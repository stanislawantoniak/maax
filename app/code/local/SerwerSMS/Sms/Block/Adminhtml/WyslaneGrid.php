<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_Block_Adminhtml_WyslaneGrid extends Mage_Adminhtml_Block_Widget_Grid_Container{
    
    public function _construct(){
        
        // gdzie jest kontroler
        $this->_controller = 'adminhtml_wyslane';
        $this->_blockGroup = 'serwersms';
        
        // tekst w naglowku admina
        $this->_headerText = 'Wiadomości wysłane';
        
        // tekst na przycisku dodawania
        $this->_addButtonLabel = '';
        parent::_construct();
        
    }
    
}

?>