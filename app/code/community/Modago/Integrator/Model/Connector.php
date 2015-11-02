<?php
/**
 * connect to modago gallery 
 */

class Modago_Integrator_Model_Connector 
    extends Varien_Object {
    
    /**
     * run process
     */
     public function run() {
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
								        $model = Mage::getModel('modagointegrator/generator_description');
									    break;
								    case $helper::FILE_PRICES:
								        $model = Mage::getModel('modagointegrator/generator_price');
									    break;
								    case $helper::FILE_STOCKS:
								        $model = Mage::getModel('modagointegrator/generator_stock');
									    break;
							    }
							    if ($model->generate()) {
    							    $model->uploadFile();
                                } 
						    }
					    }
				    }
				    break;
			    case $helper::STATUS_ERROR:
			        echo 'Error'.PHP_EOL;
				    //todo: handle error
				    break;
			    case $helper::STATUS_FATAL_ERROR:
			        echo 'Fatal error'.PHP_EOL;
				    //todo: handle fatal error
				    break;
		    }
	    } else {
	        echo 'Wrong answer'.PHP_EOL;
	    }

     }
    
} 