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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    
 * @package     _home
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Source for cron hours
 *
 * @category    Find
 * @package     Find_Feed
 */
class SolrBridge_Solrsearch_Model_Adminhtml_System_Source_Config_Cores
{

    /**
     * Fetch options array
     * 
     * @return array
     */
    public function toOptionArray()
    {
		/**
	     * Get the resource model
	     */
	    $resource = Mage::getSingleton('core/resource');
	     
	    /**
	     * Get the table name
	     */
	    $tableName = $resource->getTableName('core_config_data');
	    
	    $readConnection = $resource->getConnection('core_read');
     
    	$query = 'SELECT * FROM ' . $tableName. ' WHERE path LIKE "solrbridgeindices/%/label"';
    	
    	$results = $readConnection->fetchAll($query);
    	
		$cores = array(
			array('label' => 'None', 'value' => ''),
		);
		
		foreach ($results as $item){
			$tempArr = explode('/', $item['path']);
			$core = $tempArr[1];
			$label = $item['value'];
			$mapstores = trim( Mage::getStoreConfig('solrbridgeindices/'.$core.'/stores'), ',');
			if(!empty($label) && !empty($mapstores)){
				$cores[] = array('label' => $label, 'value' => $core);
			}
		}
    			
        return $cores;
    }
}
