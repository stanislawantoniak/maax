<?php
class Zolago_Catalog_Model_Product_Configurable_DataTest extends ZolagoDb_TestCase
{
    protected $_model;
    private $_configurableDataClass;

    private $_store;


    protected function _getModel()
    {
        if (empty($this->_model)) {
            $this->_model = Mage::getModel('zolagocatalog/product_configurable_data');
            $this->assertNotEmpty($this->_model);
        }
        return $this->_model;
    }


    public function testCreate()
    {
        $this->_getModel();
    }

    public function setUp()
    {
        $this->_configurableDataClass = Mage::getModel('zolagocatalog/product_configurable_data');
        $this->_store = 1;
    }

    public function testConfigurableDataClass()
    {
        $this->assertInstanceOf('Zolago_Catalog_Model_Product_Configurable_Data', $this->_configurableDataClass);
    }

    public function testEmulateConfigurable()
    {
        $file = Zolago_Catalog_Helper_Log::emulateConfigurable(TRUE);
        $this->assertNotEquals($file, FALSE, "File created");
    }

    /**
     * configurable price=min(simple)
     */
    public function testRecalculateConfigurablePrice()
    {
        $storeId = $this->_store;

        //1. configurable prices
        $catalogModelProductConfigurableData = Mage::getModel('zolagocatalog/product_configurable_data');
        $configurablePrices = $catalogModelProductConfigurableData->getConfigurablePrices($storeId, 10);

        $configurableProductsIds = array_keys($configurablePrices);
        //2. min simple prices
        $catalogModelProductConfigurableData = Mage::getModel('zolagocatalog/product_configurable_data');
        $minPrices = $catalogModelProductConfigurableData->getConfigurableMinPrice($configurableProductsIds,$storeId);


        $diff = array();
        if (!empty($configurablePrices)) {
            foreach ($configurablePrices as $configId => $configurablePricesItem) {

                $configPrice = $configurablePricesItem['price'];

                $configMinPrice = $minPrices[$configId]['min_price'];

                if ($configPrice !== $configMinPrice) {
                    $diff[] = $configId;
                }
            }
        }
        $this->assertEquals($diff, array(), "There are differences in prices");
    }




}