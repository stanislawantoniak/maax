<?php
/**
 * The purpose of this class is to provide clear interface
 * for all IToris Magento Extensions to IToris Installer.
 *
 * Since Installer version 1.2.4 all new itoris extensions should use this class
 * to check licenses, not the IInstaller.php file.
 * since 1.2.4
 */
class Itoris_Installer_Client{

	/**
	 * Ask itoris license server for license presents
	 * if not - drops local license record
	 * if website not specified - gets current website
	 *
	 * @static
	 * @param string $alias
	 * @param \Mage_Core_Model_Website|null $website
	 * @return bool
	 */
	public static function isRegistered($alias, Mage_Core_Model_Website $website = null){
		return true;
		if (IInstaller::isDev($alias, false)) {
			$collection = self::prepareStoresCollectionForAdmin();
		} else {
			$collection = self::prepareStoresCollectionOfWebsite($website);
		}
		$hosts = self::getHostsList($collection);
		self::cleanLicenses($alias, $hosts);
		return self::isAnyLicenseExists($alias, $hosts);
	}

	/**
	 * Lookup to the local database for license presents
	 * for alias and website without contact to the itoris server
	 * 
	 * @static
	 * @param $alias
	 * @param Mage_Core_Model_Website|null $website
	 * @return bool
	 */
	public static function isRegisteredAutonomous($alias, Mage_Core_Model_Website $website = null){
		return true;
		if (IInstaller::isDev($alias)) {
			$collection = self::prepareStoresCollectionForAdmin();
		} else {
			$collection = self::prepareStoresCollectionOfWebsite($website);
		}

		$hosts = self::getHostsList($collection);
		return self::isAnyLicenseExists($alias, $hosts);
	}

	/**
	 * This method should be used to check block admin part of
	 * module or not. If extension registered at least at one host
	 * this method returns true. Otherwise false. Also this method
	 * checks all local license records for module $alias and removes
	 * licenses that no longer valid.
	 *
	 * @static
	 * @param $alias
	 * @return bool
	 */
	public static function isAdminRegistered($alias){
		return true;
		$collection = self::prepareStoresCollectionForAdmin();
		$hosts = self::getHostsList($collection);
		self::cleanLicenses($alias, $hosts);
		return self::isAnyLicenseExists($alias, $hosts);
	}

	/**
	 * This method does the same as isAdminRegistered
	 * except it not clears licenses of the module $alias.
	 *
	 * @static
	 * @param $alias
	 * @return bool
	 */
	public static function isAdminRegisteredAutonomous($alias){
		return true;
		$collection = self::prepareStoresCollectionForAdmin();
		$hosts = self::getHostsList($collection);
		return self::isAnyLicenseExists($alias, $hosts);
	}

	/**
	 * Returns count of trial days that left
	 *
	 * @static
	 * @param $alias
	 * @return int
	 */
	public static function getTrialDaysLeft($alias){
		return IInstaller::getTrialDaysLeft($alias);
	}

	/**
	 * Returns version of the installed module with $alias
	 * as array. For example: array(1,2,4). If no module installation
	 * found returns false.
	 *
	 * @static
	 * @param $alias
	 * @return array|bool
	 */
	public static function getVersion($alias){
		return IInstaller::getVersion($alias);
	}

	/**
	 * Returns version of product with $alias as a string
	 * @static
	 * @param $alias
	 * @return string
	 */
	public static function getVersionStr($alias){
		return implode('.',self::getVersion($alias));
	}

	/**
	 * Compare $version with version of product with given $alias
	 * using $operator as compare operator
	 * @static
	 * @param string $alias
	 * @param string $version
	 * @param string $operator
	 * @return mixed
	 */
	public static function versionCompare($alias, $version, $operator){
		return version_compare(implode('.',IInstaller::getVersion($alias)), $version, $operator);
	}

	/**
	 * Registers host of the current store.
	 *
	 * @static
	 * @param $alias
	 * @param $sn
	 * @return int
	 */
	public static function registerCurrentStoreHost($alias, $sn){
		$pid = IInstaller::getPidByAlias($alias);
		return IInstaller::registerProduct($pid, $sn);
	}

	/**
	 * Installer Client initialization.
	 *
	 * @static
	 * @return void
	 */
	public static function init(){
		require_once Mage::getBaseDir().'/app/code/local/Itoris/Installer/code/IInstaller.php';
	}

	/**
	 * Goes through collection and collect all hosts, bring them to
	 * itoris api format and return as array.
	 *
	 * @static
	 * @param Itoris_Installer_Store_Collection $collection
	 * @return array
	 */
	private static function getHostsList(Itoris_Installer_Store_Collection $collection){
		return IInstaller::getHostsList($collection);
	}

	/**
	 * Prepare stores collection of $website.
	 *
	 * @static
	 * @param Mage_Core_Model_Website $website
	 * @return Itoris_Installer_Store_Collection
	 */
	private static function prepareStoresCollectionOfWebsite(Mage_Core_Model_Website $website = null){
		if($website === null){
			$website = Mage::app()->getWebsite();
		}
		/** @var $result Mage_Core_Model_Mysql4_Store_Collection */
		$result = Mage::getModel('core/store')->getCollection();
		$result->setLoadDefault(true);

		$result->addWebsiteFilter($website->getId());

		return self::convertStoresCollectionToTheInternal($result);
	}

	/**
	 * Prepare stores collection of all magento installation.
	 *
	 * @static
	 * @return Itoris_Installer_Store_Collection
	 */
	private static function prepareStoresCollectionForAdmin(){
		/** @var $result Mage_Core_Model_Mysql4_Store_Collection */
		$result = Mage::getModel('core/store')->getCollection();
		$result->setLoadDefault(true);
		return self::convertStoresCollectionToTheInternal($result);
	}

	/**
	 * @static
	 * @param $collection
	 * @return Itoris_Installer_Store_Collection
	 */
	private static function convertStoresCollectionToTheInternal($collection){
		return IInstaller::convertStoresCollectionToTheInternal($collection);
	}

	/**
	 * Check all license records of $alias product on $hosts
	 * and delete invalid ones.
	 *
	 * @static
	 * @param $alias
	 * @param array $hosts
	 * @return void
	 */
	private static function cleanLicenses($alias, array $hosts){
		IInstaller::checkRegistrations($alias, $hosts);
	}

	/**
	 * Check is local database contains any license for specified product
	 * that registered on one of $hosts.
	 *
	 * @static
	 * @param $alias
	 * @param array $hosts
	 * @return bool
	 */
	private static function isAnyLicenseExists($alias, array $hosts){
		return count(IInstaller::getLicensesData($alias, $hosts)) > 0;
	}
}

Itoris_Installer_Client::init();

?>