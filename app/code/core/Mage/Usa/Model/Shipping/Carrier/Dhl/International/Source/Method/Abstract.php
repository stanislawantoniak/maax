<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Usa
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Source model for DHL shipping methods
 *
 * @category   Mage
 * @package    Mage_Usa
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Usa_Model_Shipping_Carrier_Dhl_International_Source_Method_Abstract
{
    /**
     * Carrier Product Type Indicator
     *
     * @var string $_contentType
     */
    protected $_contentType;

    /**
     * Show 'none' in methods list or not;
     *
     * @var bool
     */
    protected $_noneMethod = false;

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        /* @var $carrierModel Mage_Usa_Model_Shipping_Carrier_Dhl_International */
        $carrierModel   = Mage::getSingleton('usa/shipping_carrier_dhl_international');
        $dhlProducts    = $carrierModel->getDhlProducts($this->_contentType);

        $options = array();
        foreach ($dhlProducts as $code => $title) {
            $options[] = array('value' => $code, 'label' => $title);
        }

        if ($this->_noneMethod) {
            array_unshift($options, array('value' => '', 'label' => Mage::helper('usa')->__('None')));
        }

        return $options;
    }
}
