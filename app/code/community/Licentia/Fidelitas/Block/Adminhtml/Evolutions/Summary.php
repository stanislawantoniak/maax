<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International  
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International 
 */
class Licentia_Fidelitas_Block_Adminhtml_Evolutions_Summary extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_evolutions_summary';
        $this->_blockGroup = 'fidelitas';
        $this->_headerText = $this->__('Segments Evolutions Summary');
        if ($segment = Mage::registry('current_segment')) {

            $this->_headerText = $this->__('Segments Evolutions Summary') . ' / ' . $segment->getName();

            $cancelUrl = $this->getUrl('*/fidelitas_segments', array('id' => $segment->getId()));
            $recordsUrl = $this->getUrl('*/fidelitas_segments/records', array('id' => $segment->getId()));
            $conversionsUrl = $this->getUrl('*/fidelitas_conversions/consegments', array('id' => $segment->getId()));

            $this->addButton('back_button', array('label' => $this->__('Back'),
                'onclick' => "window.location='$cancelUrl';", 'class' => 'back'));

            $this->addButton('records_button', array('label' => $this->__('Records'),
                'onclick' => "window.location='$recordsUrl';"));

            $this->addButton('conversions_button', array('label' => $this->__('Conversions'),
                'onclick' => "window.location='$conversionsUrl';"));



            $url = $this->getUrl('*/fidelitas_segments/records', array('refresh' => '1', 'id' => $segment->getId()));
            $text = $this->__('This may take a few minutes if you have thousands of subscribers. Continue?');

            $this->addButton('send', array('label' => $this->__('Refresh Segment'),
                'class' => 'save',
                'onclick' => "if(!confirm('$text')){return false;}; window.location='$url'"));
        }
        parent::__construct();

        $this->_removeButton('add');
    }

}
