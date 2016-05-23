<?php

class ZolagoOs_OutsideStore_Helper_Data extends Mage_Core_Helper_Abstract {
	
	private function getConfigIsGallery() {
		$flag = (string)Mage::getConfig()->getNode('global/is_gallery');
		return $flag;
	}

	/**
	 * is_gallery flag tell us to use functionality from Modago(true) or not(false)
	 * if false this will be hidden/blocked:
	 *
	 * portal vendora:
	 * 		rozliczenia
	 * 		warunki współpracy
	 * 		na ekranie logowania - nowe konto
	 *
	 * admin: rejestracje sklepów
	 * 		recenzje sklepów
	 * 		koszty marketingu
	 * 		rozliczenia
	 * 		salda
	 * 		faktury
	 * 		wypłaty
	 * 		konfiguracje z tym związane
	 *
	 * @return bool
	 */
	public function useGalleryConfiguration() {
		if ($this->getConfigIsGallery() === "false") {
			return false;
		}
		return true;
	}
}