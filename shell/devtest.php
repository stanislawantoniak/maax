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
        return "use ex: php -f shell/devtest.php -action tierCommission -poid 26";
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
        return "use ex: php -f shell/devtest.php -action testCheckProduct -pid 26";
    }

	/**
	 * Process solr search queue like before AOE scheduler
	 */
	public function solrAction() {
		/** @var Zolago_Solrsearch_Model_Queue $model */
		$model = Mage::getSingleton('zolagosolrsearch/queue');
		$model->process();
	}

	public function solrActionHelp() {
		return "use ex: php -f shell/devtest.php -action solr";
	}

	public function updateStockConverterAction() {
		/** @var Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 $obj */
		$obj = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
		$liczbaA1 = 5;
		$liczbaB1 = 6;


		$liczbaA2 = 6;
		$liczbaB2 = 5;

		$param =array (
			10 => array (
				'10-04J462-4-011' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA1,
					'MAGAZYN' => $liczbaB1,
					'k99' => 0
				),
				'10-04J462-4-012' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA1,
					'MAGAZYN' => $liczbaB1,
					'k99' => 0
				),
				'10-04J462-4-013' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA1,
					'MAGAZYN' => $liczbaB1,
					'k99' => 0
				),
				'10-04J462-4-014' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA1,
					'MAGAZYN' => $liczbaB1,
					'k99' => 0
				),

				'10-04J462-4-015' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA1,
					'MAGAZYN' => $liczbaB1,
					'k99' => 0
				),

				'10-04J462-4-016' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA1,
					'MAGAZYN' => 0,
					'k99' => 0
				),



				//10-04B163A5-01
				'10-04B163A5-010' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA2,
					'MAGAZYN' => $liczbaB2,
					'k99' => 0
				),
				'10-04B163A5-011' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA2,
					'MAGAZYN' => $liczbaB2,
					'k99' => 0
				),
				'10-04B163A5-012' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA2,
					'MAGAZYN' => $liczbaB2,
					'k99' => 0
				),
				'10-04B163A5-013' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA2,
					'MAGAZYN' => $liczbaB2,
					'k99' => 0
				),

				'10-04B163A5-014' => array(
					'SKLEP2' => 0,
					'SKLEP1' => $liczbaA2,
					'MAGAZYN' => $liczbaB2,
					'k99' => 0
				),

			),
		);
		$obj::updateStockConverter($param);
	}

	public function updateStockConverterActionHelp() {
		return "use ex: php -f shell/devtest.php -action updateStockConverter";
	}
}

$shell = new Aoe_Scheduler_Shell_Scheduler();
$shell->run();