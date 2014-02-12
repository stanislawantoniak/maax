<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipPayout_Model_Payout_Adjustment extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'udpayout_payout_adjustment';
    protected $_eventObject = 'adjustment';

    protected function _construct()
    {
        $this->_init('udpayout/payout_adjustment');
        parent::_construct();
    }
}
