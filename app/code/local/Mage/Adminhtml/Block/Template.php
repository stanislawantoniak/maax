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