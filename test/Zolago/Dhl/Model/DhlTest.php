<?php
class Zolago_Dhl_Model_DhlTest extends ZolagoDb_TestCase {
	
	protected static $_dhlSoap;

	public function __construct() {
		parent::__construct();
		
		//Mock SOAP Service to prevent real life calls
        if (self::$_dhlSoap === null) {
            self::$_dhlSoap = $this->getMockFromWsdl('https://testowy.dhl24.com.pl/webapi');
        }
        return self::$_dhlSoap;	
    }	
	
    public function testCreate() {
		$posModel		= Zolago_Dhl_Helper_Test::getPos();
		$operatorModel	= Zolago_Dhl_Helper_Test::getOperator();	
		
        $model = new Zolago_Dhl_Model_Dhl($posModel, $operatorModel);
		
        $this->assertNotEmpty($model);
		$this->assertEquals('Zolago_Dhl_Model_Dhl',get_class($model));
    }
	
    protected function _getModel() {
        if (!$this->_model) {
            $model = Mage::getModel('zolagodhl/dhl');
            $this->assertNotEmpty($model->getId());
            $this->_model = $model;
        }
        return $this->_model;
    }
	
    public function testGetTrackAndTraceInfo() {
		if (!no_coverage()) {
			$this->markTestSkipped('coverage');
			return;
		}
		
		$soapResult = new StdClass;
		$soapResult->shipmentId = '11122223333';
		$soapResult->receivedBy = '';
		$soapResult->events = new StdClass();

        self::$_dhlSoap->expects($this->any())
					->method('getTrackAndTraceInfo')
                    ->will($this->returnValue($soapResult));
		
		$this->assertEquals(
			$soapResult,
			self::$_dhlSoap->getTrackAndTraceInfo(
				'11122223333',
				'',
				new StdClass()
			)
        );
	}
	
    public function testGetMyShipments() {
		if (!no_coverage()) {
			$this->markTestSkipped('coverage');
			return;
		}
		
		$soapResult = new StdClass;
		$soapResult->getMyShipmentsResult = new StdClass();

        self::$_dhlSoap->expects($this->any())
					->method('getMyShipments')
                    ->will($this->returnValue($soapResult));
		
		$this->assertEquals(
			$soapResult,
			self::$_dhlSoap->getMyShipments(
				'2014-01-01',
				'2014-03-10',
				100
			)
        );
	}	
}