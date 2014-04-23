<?php

/**
 * Fix do dziedziczenia po oryginalnej klasie
 * Odkomentowac w razei potrzby oryginal
 */

$fileContent = preg_replace(
		"/Mage_Adminhtml_Block_Abstract/", 
		"Mage_Adminhtml_Block_Abstract_Tmp", 
		file_get_contents(
				Mage::getModuleDir("block", "Mage_Adminhtml") . DS . 
				"Block". DS. "Abstract.php"
		));

eval('?>' . $fileContent);

class Mage_Adminhtml_Block_Abstract extends Mage_Adminhtml_Block_Abstract_Tmp
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
