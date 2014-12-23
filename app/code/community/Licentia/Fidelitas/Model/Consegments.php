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
class Licentia_Fidelitas_Model_Consegments extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/consegments');
    }

    public function getInfoForSegmentCustomer($customerId, $segmentId, $listnum) {

        $result = $this->getCollection()
                ->addFieldToSelect('order_amount')
                ->addFieldToFilter('listnum', $listnum)
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('segment_id', $segmentId);

        $data = array();
        $data['conversions_number'] = $result->count();
        $data['conversions_amount'] = 0;
        $data['conversions_average'] = 0;

        foreach ($result as $item) {
            $data['conversions_amount'] += $item->getData('order_amount');
        }

        if ($data['conversions_number'] > 0) {
            $data['conversions_average'] = $data['conversions_amount'] / $data['conversions_number'];
        }

        return $data;
    }

}
