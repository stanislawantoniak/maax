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
        $msg = 'OK';
        if(is_array($response) && isset($response['status'])) {
            switch($response['status']) {
            case $helper::STATUS_OK:
                if(!isset($response['files'])) {
                    //nothing to generate, everything is ok
                    return;
                } else {
                    $fileTypes = $helper->getFileTypes();
                    foreach($response['files'] as $file) {
                        try {
                            if(in_array($file,$fileTypes)) {
                                $helper->log('Generate file: '.$file);
                                $model = $helper->createGenerator($file);
                                if ($model->generate()) {
                                    $model->compress();
                                    $model->setFtpUrl($response['ftp_url'])->uploadFile();
                                }
                                $helper->log('Process file finish: '.$file);
                            } else {
                                $msg = 'Wrong file type '.$file;
                            }
                        } catch (Exception $xt) {
                            $helper->log($xt->getMessage());
                        }
                    }
                }
                break;
            case $helper::STATUS_ERROR:
                $msg = 'Error';
                break;
            case $helper::STATUS_FATAL_ERROR:
                $msg = 'Fatal error';
                break;
            }
        } else {
            $msg = 'Wrong answer';
        }
        $helper->log(serialize($response));
        $helper->log($msg);
        echo $msg.PHP_EOL;

    }

}