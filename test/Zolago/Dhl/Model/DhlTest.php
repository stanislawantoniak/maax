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
        $this->_getModel();
        $model = Mage::getModel('zolagodhl/carrier');
        $this->assertNotEmpty($model);
        $model = Mage::getModel('zolagodhl/carrier/tracking');
        $this->assertNotEmpty($model);
    }
	
    protected function _getModel() {
        if (!$this->_model) {
            $model = Mage::getModel('zolagodhl/client');
            $this->assertNotEmpty($model);
            $model->setAuth('CONVERTICA','yezxCGQ2bFYCWr');
            $this->_model = $model;
        }
        return $this->_model;
    }
    public function testGetModelTrackAndTraceInfo() {
        $model = $this->_getModel();
        $ret = $model->getTrackAndTraceInfo('11898773100');
        $this->assertNotEmpty($ret);
        $this->assertInstanceOf('StdClass',$ret);
    }	

    public function testGetTrackAndTraceInfo() {
		
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
    public function testGetModelMyShipments() { 
        $model = $this->_getModel();
        $ret = $model->getMyShipments(date('Y-m-d',time()-40*24*3600),date('Y-m-d'));
        $this->assertNotEmpty($ret);
        $this->assertInstanceOf('StdClass',$ret,print_R($ret,1));
        $ret = $model->getMyShipments(date('Y-m-d',time()-140*24*3600),date('Y-m-d'));
        $this->assertNotEmpty($ret);
        $this->assertInternalType('array',$ret,print_R($ret,1));
    }	
    public function testGetMyShipments() {
		
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
	public function testCreateShipments() { 
	    return;
	    $shipment = new Zolago_Dhl_Mock_Shipment('cashondelivery');
	    $model = $this->_getModel();
	    $shipmentSettings = array (
	        'width' => 10,
	        'height' => 10,
	        'length' => 10,
	        'weight' => 10,
	        'quantity' => 1,
	        'type' => 'PACKAGE'
        );
	    $ret = $model->createShipments($shipment,$shipmentSettings);	    
	}
}