<?php
/**
 * api cron controller
 */
class Modago_Integrator_RunApiCronController extends Modago_Integrator_Controller_Abstract {
    /**
     * run api cron
     */
     public function indexAction() {
        $this->_checkAuthorization();
        $helper = Mage::helper('modagointegrator/api');
        $mutex = $helper->getMutex('api.tmp');
        if (!$mutex->lock()) {
            echo 'Process already running'.PHP_EOL;
            exit;
        }

        /** @var Modago_Integrator_Model_Connector $connector */
        $connector = Mage::getModel('modagointegrator/api');
        $connector->run();
        $mutex->unlock();
     }     

}