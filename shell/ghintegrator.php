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

        if ($this->getArg('generate')) {
            $secret = $this->getArg('secret');
            $externalId = $this->getArg('external_id');

            Mage::getModel("ghintegrator/communication")->connect($secret, $externalId);
        } else {
            echo $this->usageHelp();
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