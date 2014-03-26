<?php

/**
 * Fix do dziedziczenia po oryginalnej klasie
 * Odkomentowac w razei potrzby oryginal
 */

$fileContent = preg_replace(
		"/Mage_Adminhtml_Block_Template/", 
		"Mage_Adminhtml_Block_Template_Tmp", 
		file_get_contents(
				Mage::getModuleDir("block", "Mage_Adminhtml") . DS . 
				"Block". DS. "Template.php"
		));

eval('?>' . $fileContent);

class Mage_Adminhtml_Block_Template extends Mage_Adminhtml_Block_Template_Tmp
{
    /**
     * Fixed
     *
     * @return string
     */
    protected function _getUrlModelClass() {
		if($this->getAsFrontend() || Mage::registry('as_frontend')){
			return 'core/url';
		}
		return 'adminhtml/url';
	}

}

//class Mage_Adminhtml_Block_Template extends Mage_Core_Block_Template
//{
//    /**
//     * Fixed
//     *
//     * @return string
//     */
//    public function _getUrlModelClass() {
//		if($this->getAsFrontend() || Mage::registry('as_frontend')){
//			return 'core/url';
//		}
//		return 'adminhtml/url';
//	}
//
//    /**
//     * Retrieve Session Form Key
//     *
//     * @return string
//     */
//    public function getFormKey()
//    {
//        return Mage::getSingleton('core/session')->getFormKey();
//    }
//
//    /**
//     * Check whether or not the module output is enabled
//     *
//     * Because many module blocks belong to Adminhtml module,
//     * the feature "Disable module output" doesn't cover Admin area
//     *
//     * @param string $moduleName Full module name
//     * @return boolean
//     */
//    public function isOutputEnabled($moduleName = null)
//    {
//        if ($moduleName === null) {
//            $moduleName = $this->getModuleName();
//        }
//        return !Mage::getStoreConfigFlag('advanced/modules_disable_output/' . $moduleName);
//    }
//
//    /**
//     * Prepare html output
//     *
//     * @return string
//     */
//    protected function _toHtml()
//    {
//        Mage::dispatchEvent('adminhtml_block_html_before', array('block' => $this));
//        return parent::_toHtml();
//    }
//
//}

