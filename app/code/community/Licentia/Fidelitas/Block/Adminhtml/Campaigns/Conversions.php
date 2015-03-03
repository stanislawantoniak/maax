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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Conversions extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_campaigns_conversions';
        $this->_blockGroup = 'fidelitas';
        $this->_headerText = $this->__('Campaign Conversions');
        if ($campaign = Mage::registry('current_campaign')) {

            $this->_headerText = $this->__('Campaign Conversions') . ' / ' . $campaign->getInternalName();

            $cancelUrl = $this->getUrl('*/fidelitas_campaigns');

            $this->addButton('cancel_campaign', array('label' => $this->__('Back'),
                'onclick' => "window.location='$cancelUrl';", 'class' => 'back'));
        }
        parent::__construct();

        $this->_removeButton('add');
    }

}
