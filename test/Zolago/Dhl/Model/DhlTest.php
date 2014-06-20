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
    protected function _getModelTrackAndTraceInfo() {
        $model = $this->_getModel();
        $ret = $model->getTrackAndTraceInfo('11898773100');
        return $ret;
    }
    public function testGetModelTrackAndTraceInfo() {
        $ret = $this->_getModelTrackAndTraceInfo();
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
        $addressData = array (
            'firstname' => 'Jan',
            'lastname' => 'Kowalski',
            'postcode' => '04-669',
            'city' => 'Paździochowo',
            'street' => 'Dziurawa 9',
            'telephone' => '999888777',
            'email' => 'janf0@interia.pl',
        );
        $pos = Zolago_Pos_Helper_Test::getPos();
        $pos->setData(Zolago_Pos_Helper_Test::getPosData());
        $model->setPos($pos);
        $model->setAddressData($addressData);
        $this->assertFalse($model->createShipments(array(),$shipmentSettings));
	    $ret = $model->createShipments($shipment,$shipmentSettings);	    
	    $this->assertNotEmpty($ret);
	}
    /**
        * @expectedException Mage_Core_Exception
        * @expectedExceptionMessage Too many shipments in one query
    */
        public function testLabels() {
	    $model = $this->_getModel();
	    $this->assertFalse($model->getLabels(null));
	    $tracking = new Zolago_Dhl_Mock_Tracking('adfadfadsfaf');
	    $ret = $model->getLabels($tracking);
	    // errors expected
	    $this->assertInternalType('array',$ret);
	    $this->assertArrayHasKey('error',$ret);
	    // too much trackings
	    $track = array (
	        $tracking,
	        $tracking,
	        $tracking,
	        $tracking,
        );
        $model->getLabels($track);
	}
	public function testProcessShipmentResults() {
	    $shipmentError = array (
	        'error' => 'Error'
        );
        $model = $this->_getModel();
        $ret = $model->processDhlShipmentsResult('test',$shipmentError);
        $this->assertInternalType('array',$ret);
        $this->assertFalse($ret['shipmentId']);
        $shipment = new StdClass();
        $shipment->createShipmentsResult = new StdClass();
        $ret = $model->processDhlShipmentsResult('test',$shipment);
        $this->assertInternalType('array',$ret);
        $this->assertFalse($ret['shipmentId']);
        $this->assertEquals($ret['message'],'DHL Service Error: test');
        $shipment->createShipmentsResult->item = new StdClass();
        $shipment->createShipmentsResult->item->shipmentId = 10;
        $ret = $model->processDhlShipmentsResult('test',$shipment);
        $this->assertInternalType('array',$ret);
        $this->assertEquals(10,$ret['shipmentId']);
	}
	public function testProcessLabelResults() {
	    $labelError = array (
	        'error' => 'Error',
        );
        $model = $this->_getModel();
        $ret = $model->processDhlLabelsResult('test',$labelError);
        $this->assertInternalType('array',$ret);
        $this->assertFalse($ret['status']);
        
        $label = new StdClass();
        $label->getLabelsResult = new StdClass();
        $ret = $model->processDhlLabelsResult('test',$label);
        $this->assertInternalType('array',$ret);
        $this->assertFalse($ret['status']);
        $this->assertEquals('DHL Service Error: test',$ret['message']);
        
        $label->getLabelsResult->item = new StdClass();
        $label->getLabelsResult->item->shipmentId = 10;        
        $label->getLabelsResult->item->labelName = 'test';
        $label->getLabelsResult->item->labelData = 'dGVzdA==';
        
        
        $ret = $model->processDhlLabelsResult('test',$label);
        $this->assertInternalType('array',$ret);
        $expected = array(
            'status' => 10,
            'message' => 'Shipment ID: 10',
            'labelName' => 'test',
            'labelData' => 'test',
        );
        $this->assertEquals($expected,$ret);
	}
	public function testRma() {
	    $helper = Mage::helper('zolagorma');
	    $helper->rmaTracking();
	}
}