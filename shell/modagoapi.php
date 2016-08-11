<?php
/**
 * Modago_Integrator_Shell
 */

require_once 'abstract.php';

class Modago_Integrator_Shell extends Mage_Shell_Abstract
{
    public function run()
    {
        $helper = Mage::helper('modagointegrator/api');
        $mutex = $helper->getMutex('api.tmp');
        // set cache flag
        if (!$mutex->lock()) {
            echo 'Process already running'.PHP_EOL;
            exit;
        }
        Mage::app()->getTranslator()->init('adminhtml', true);
        if (!Mage::isInstalled()) {
            echo "Application is not installed yet, please complete install wizard first.";
            exit;
        }

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        set_time_limit(36000);
        /** @var Modago_Integrator_Model_Connector $connector */
        $connector = Mage::getModel('modagointegrator/api');
        $connector->run();
        $mutex->unlock();
    }


    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
    Usage:  php -f modagoapi.php 
USAGE;
    }
}

$shell = new Modago_Integrator_Shell();
$shell->run();