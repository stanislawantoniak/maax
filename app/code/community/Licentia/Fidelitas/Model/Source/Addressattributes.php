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
class Licentia_Fidelitas_Model_Source_Addressattributes {

    public function toOptionArray() {
        $type = Mage::getModel('eav/entity_type')->loadByCode('customer_address');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($type);
        $return = array();

        foreach ($attributes as $attribute) {
            $return[] = array('value' => $attribute['attribute_code'], 'label' => $attribute['frontend_label']);
        }

        return $return;
    }

}
