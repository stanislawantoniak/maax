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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Edit_Tab_Followsent extends Licentia_Fidelitas_Block_Adminhtml_Campaigns_Children_Grid {

    protected function _prepareCollection() {

        $followup = Mage::registry('current_followup');

        $collection = Mage::getModel('fidelitas/campaigns')
                ->getResourceCollection()
                ->addFieldToFilter('followup_id', array('in' => $followup->getAllIds()));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

}
