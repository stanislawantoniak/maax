<?php
/**
 * GH_Integrator_Shell
 */

require_once 'abstract.php';

class GH_Integrator_Shell extends Mage_Shell_Abstract
{
    public function run()
    {
        if (!Mage::isInstalled()) {
            echo "Application is not installed yet, please complete install wizard first.";
            exit;
        }

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        set_time_limit(36000);

	    /** @var Modago_Integrator_Helper_Data $helper */
	    $helper = Mage::helper("modagointegrator");

	    /** @var Modago_Integrator_Model_Client $client */
	    $client = Mage::getModel("modagointegrator/client");
        $response = $client->getResponse();

	    if(is_array($response) && isset($response['status'])) {
		    switch($response['status']) {
			    case $helper::STATUS_OK:
				    if(!isset($response['files'])) {
					    //nothing to generate, everything is ok
					    return;
				    } else {
					    $fileTypes = $helper->getFileTypes();
					    foreach($response['files'] as $file) {
						    if(in_array($file,$fileTypes)) {
							    switch($file) {
								    case $helper::FILE_DESCRIPTIONS:
									    //todo: generate descriptions file
									    //todo: upload file
									    break;
								    case $helper::FILE_PRICES:
									    //todo: generate prices file
									    //todo: upload file
									    break;
								    case $helper::FILE_STOCKS:
									    //todo: generate stocks file
										//todo: upload file
									    break;
							    }
						    }
					    }
				    }
				    break;
			    case $helper::STATUS_ERROR:
				    //todo: handle error
				    break;
			    case $helper::STATUS_FATAL_ERROR:
				    //todo: handle fatal error
				    break;
		    }
	    }
    }


    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
    Usage:  php -f ghintegrator.php -- [options]

        --generate all             Generate all
        --generate stock           Generate stock
        --generate price           Generate price
        --generate description     Generate product description

USAGE;
    }
}

$shell = new GH_Integrator_Shell();
$shell->run();