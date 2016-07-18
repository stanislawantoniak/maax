<?php

class Zolago_Turpentine_Model_Varnish_Configurator_Version4 extends Nexcessnet_Turpentine_Model_Varnish_Configurator_Version4 {

	/**
	 * Get the full path for a given template filename
	 *
	 * @param  string $baseFilename
	 * @return string
	 */
	protected function _getVclTemplateFilename($baseFilename) {
		$extensionDir = Mage::getModuleDir('', 'Nexcessnet_Turpentine');
		return sprintf('%s/misc/%s', $extensionDir, $baseFilename);
	}

	protected function _getTemplateVars() {
		$vars = parent::_getTemplateVars();
		// Override esi private ttl - is real magento cookie time - always!
		$vars['esi_private_ttl'] = Mage::helper('turpentine/esi')->getSystemCookieLifeTime();
		return $vars;
	}

}
