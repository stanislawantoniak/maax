<?php

require_once 'abstract.php';

class Aoe_Scheduler_Shell_Scheduler extends Mage_Shell_Abstract {

    /**
     * Run script
     *
     * @return void
     */
    public function run() {
        $action = $this->getArg('action');
        if (empty($action)) {
            echo $this->usageHelp();
        } else {
            $actionMethodName = $action.'Action';
            if (method_exists($this, $actionMethodName)) {
                $this->$actionMethodName();
            } else {
                echo "Action $action not found!\n";
                echo $this->usageHelp();
                exit(1);
            }
        }
    }


    /**
     * Retrieve Usage Help Message
     *
     * @return string
     */
    public function usageHelp() {
        $help = 'Available actions: ' . "\n";
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (substr($method, -6) == 'Action') {
                $help .= '    -action ' . substr($method, 0, -6);
                $helpMethod = $method.'Help';
                if (method_exists($this, $helpMethod)) {
                    $help .= $this->$helpMethod();
                }
                $help .= "\n";
            }
        }
        return $help;
    }

    public function tierCommissionAction() {
        $poid = $this->getArg('poid');
        $po = Mage::getModel("zolagopo/po")->load($poid);
        Mage::helper("udtiercom")->processPo($po);
    }

    public function tierCommissionActionHelp() {
        return "use ex: php shell/devtest.php -action tierCommission -poid 100";
    }

    public function testCheckProductAction() {
        $pid = $this->getArg('pid');
        /** @var Zolago_Catalog_Model_Product $product */
        $product = Mage::getModel("zolagocatalog/product")->load($pid);

        var_dump($product->getData('description_status'));
        var_dump($product->getPrice());
        var_dump($product->getIsProductCanBeEnabled());
    }

    public function testCheckProductActionHelp() {
        return "use ex: php -f shell/devtest -action testCheckProduct -pid 26";
    }
}

$shell = new Aoe_Scheduler_Shell_Scheduler();
$shell->run();