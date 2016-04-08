<?php
/**
 * Zolago_Pos_Pos
 */

require_once 'abstract.php';

class Zolago_Pos_Pos extends Mage_Shell_Abstract
{
    public function run()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        Zolago_Pos_Model_Observer::setAppropriatePoPos();
    }


}

$shell = new Zolago_Pos_Pos();
$shell->run();