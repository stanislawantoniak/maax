<?php

require_once 'abstract.php';

class Snowdog_Freshmail_Shell extends Mage_Shell_Abstract
{
    public function run()
    {
        if ($this->getArg('sync')) {
            try {
                $totalProcessed = $this->_factory->getModel('snowfreshmail/cron')->runSubscribersSyncBatch();
                echo sprintf('Processed subscribers: %s', $totalProcessed) . PHP_EOL;
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } elseif ($this->getArg('syncAll')) {
            $flag = Mage::getModel('snowfreshmail/flag_sync')->loadSelf();
            $flag->delete();
            try {
                do {
                    $totalProcessed = $this->_factory->getModel('snowfreshmail/cron')->runSubscribersSyncBatch();
                    echo sprintf('Processed subscribers: %s', $totalProcessed) . PHP_EOL;
                } while ($totalProcessed == Snowdog_Freshmail_Model_Cron::SYNC_BATCH_LIMIT);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE

Usage:  php freshmail.php -- [options]

  sync          Sync subscribers in a batch mode
  syncAll       Sync all subscribers at once


USAGE;
    }
}

$shell = new Snowdog_Freshmail_Shell();
$shell->run();
