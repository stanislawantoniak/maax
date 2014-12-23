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
class Licentia_Fidelitas_Block_Adminhtml_Segments_Records extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_segments_records';
        $this->_blockGroup = 'fidelitas';
        parent::__construct();

        $this->_removeButton('add');

        if ($segment = Mage::registry('current_segment')) {

            $this->_headerText = $segment->getname() . ' / ' . $this->__('Segments');

            $urlBack = $this->getUrl('*/*/');
            $this->addButton('back', array('label' => $this->__('Back'),
                'class' => 'back',
                'onclick' => "window.location='$urlBack'"));

            $url = $this->getUrl('*/fidelitas_segments/records', array('refresh' => '2', 'id' => $segment->getId()));
            $text = $this->__('This will refresh this segment next time your cron runs. Continue?');
            $this->addButton('background_refresh', array('label' => $this->__('Refresh Segment'),
                'class' => 'save',
                'onclick' => "if(!confirm('$text')){return false;}; window.location='$url'"));
        }
    }

}
