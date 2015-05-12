<?php
class Zolago_Turpentine_Model_Varnish_Configurator_Version3
    extends Nexcessnet_Turpentine_Model_Varnish_Configurator_Version3 {
	
	/**
	 * Use custom file
	 * @param type $baseFilename
	 * @return type
	 */
	protected function _getVclTemplateFilename( $baseFilename ) {
		if($baseFilename=="version-3.vcl"){
			$extensionDir = Mage::getModuleDir( '', 'Zolago_Turpentine' );
			return sprintf( '%s/misc/%s', $extensionDir, $baseFilename );
		}
		return parent::_getVclTemplateFilename($baseFilename);
    }
	
	protected function _getTemplateVars() {
		$vars = parent::_getTemplateVars();
		// Override esi private ttl - is real magento cookie time - always!
        $vars['esi_private_ttl'] = 
				Mage::helper( 'turpentine/esi' )->getSystemCookieLifeTime();
        return $vars;
	}

	protected function _getCategoryUrlExcludes() {
		$categories = Mage::getModel('catalog/category')
			->getCollection()
			->addAttributeToSelect('*');

		$out = array();
		foreach($categories as $cat) {
			/** @var $cat Zolago_Catalog_Model_Category */
			$out[] = $cat->getUrlPath();
		}

		Mage::log($out,null,'varnishCat.log');

		return implode('|',$out);
	}

}
