<?php

/**
 * Nexcess.net Turpentine Extension for Magento
 * Copyright (C) 2012  Nexcess.net L.L.C.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

abstract class Zolago_Turpentine_Model_Varnish_Configurator_Abstract extends Nexcessnet_Turpentine_Model_Varnish_Configurator_Abstract {

	/**
	 * Format the URL exclusions for insertion in a regex. Admin frontname and
	 * API are automatically added.
	 *
	 * @return string
	 */
	protected function _getUrlExcludes() {
		$urls = Mage::getStoreConfig( 'turpentine_vcl/urls/url_blacklist' );
		return implode( '|', array_merge( array( $this->_getAdminFrontname(), 'api' ),
			Mage::helper( 'turpentine/data' )->cleanExplode( PHP_EOL, $urls ),
			$this->_getCategoryUrlExcludes() ) );
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

		return $out;
	}
}
