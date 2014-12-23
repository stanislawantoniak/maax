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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_campaigns';
        $this->_blockGroup = 'fidelitas';
        $this->_headerText = $this->__('Campaigns');
        $this->_addButtonLabel = $this->__('New Email Campaign');
        parent::__construct();

        $this->_updateButton('add', 'onclick', "setLocation('{$this->getUrl("*/*/new/type/email")}')");

        $data = array('label' => $this->__('New SMS Campaign'), 'class' => 'add', 'onclick' => "setLocation('{$this->getUrl("*/*/new/type/sms")}')");
        $this->_addButton('add_sms', $data);
    }

}
