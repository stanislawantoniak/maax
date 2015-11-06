<?php
/**
 * Modago_Integrator_Shell
 */

require_once 'abstract.php';

class Modago_Integrator_Shell extends Mage_Shell_Abstract
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
        /** @var Modago_Integrator_Model_Connector $connector */
        $connector = Mage::getModel('modagointegrator/connector');
        $connector->run();
    }


    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
    Usage:  php -f modagointegrator.php -- [options]

        --generate all             Generate all
        --generate stock           Generate stock
        --generate price           Generate price
        --generate description     Generate product description

USAGE;
    }
}

$shell = new Modago_Integrator_Shell();
$shell->run();