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
 * @package     Mage_Install
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Installer data model
 *
 * @category   Mage
 * @package    Mage_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Install_Model_Installer_Data extends Varien_Object
{

    /**
     * Errors array
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Add error
     *
     * @param string $error
     * @return Mage_Install_Model_Installer_Data
     */
    public function addError($error)
    {
        $this->_errors[] = $error;
        return $this;
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

}
