<?php

require_once 'abstract.php';


class License extends Mage_Shell_Abstract {

	/**
	 * Run script
	 *
	 * @return void
	 */
	public function run() {
        ZolagoOs_SimpleLicense_Helper_Data::validateLicense();	    
	    exit(1);
	}


}
$shell = new License();
$shell->run();