<?php
/**
 * lp master controller
 */
abstract class Orba_Shipping_Controller_Lp extends Mage_Core_Controller_Front_Action
{	
    protected $_helper;
	public function indexAction() {
		$this->_redirect('/');
	}

	abstract protected function _getHelper();
	abstract protected function _getCarrierCode();
	abstract protected function _getLpDownloadUrl();
	abstract protected function _getSettings();
	
	public function lpAction() {
		$dhlFile			= false;
		$result	= array(
			'status'	=> false,
			'file'		=> false,
			'message'	=> Mage::helper('zolagopo')->__('%s Service Error. Failed to get waybill.',$this->_getCarrierCode())
		);
		$request = $this->getRequest();
		if ($request->getParam('trackNumber')) {
			$trackNumber = $request->getParam('trackNumber');
			$ioAdapter = new Varien_Io_File();
			$helper = $this->_getHelper();
			$file = $helper->getIsFileAvailable($trackNumber);
			if (!$file) {
				$webCall = $this->_lpCall($request);
				if ($webCall['status']) {
					$file = $helper->getIsFileAvailable($trackNumber);
				} else {
					$result['message'] = $webCall['message'];
				}
			}
		}

		if ($file) {
			$result['status']	= true;
			$result['file']		= $trackNumber;
			$result['url']		= Mage::getUrl($this->_getLpDownloadUrl(), array("fileName"=>$trackNumber));
			unset($result['message']);
		}
		
		$resultJSON = Mage::helper('core')->jsonEncode($result);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($resultJSON);
	}
	
	public function lpDownloadAction() {
		$dhlFile = false;
		
		$request = $this->getRequest();
		if ($request->getParam('fileName')) {
			$dhlFileName = $request->getParam('fileName');
			$ioAdapter = new Varien_Io_File();
			$file = $this->_getHelper()->getIsFileAvailable($dhlFileName);
			if ($file) {
				return $this->_prepareDownloadResponse(basename($file), @$ioAdapter->read($file), 'application/pdf');
			}
		}
		$this->_redirectReferer();
	}	
	
	protected function _lpCall($request)
	{
		$dhlFile	= array(
			'status'	=> false,
			'file'		=> false,
			'message'	=> false
		);
		
		$vendorId	= $request->getParam('vId');
		$posId		= $request->getParam('posId');
		$trackId	= $request->getParam('trackId');
		$udpoId		= $request->getParam('udpoId');
		
		$vendorModel	= Mage::getModel('udropship/vendor')->load($vendorId);
		$trackModel		= Mage::getModel('sales/order_shipment_track')->load($trackId);
		$udpoModel		= Mage::getModel('udpo/po')->load($udpoId);
		
        $settings = $this->_getSettings();
		$client		= $this->_getHelper()->startClient($settings);
		$result		= $client->getLabels($trackModel);
		$result			= $client->processLabelsResult('getLabels', $result);
		if ($result['status']) {
			$ioAdapter			= new Varien_Io_File();
			$fileName			= $result['labelName'];
			$fileContent		= $result['labelData'];
			$fileLocation		= $this->_getHelper()->getFileDir() . $fileName;
			$file['status']	= true;
			$file['file']	= @$ioAdapter->filePutContent($fileLocation, $fileContent);
		} else {
			//Error Scenario
			$this->_getHelper()->addUdpoComment($udpoModel, $result['message'], false, true, false);
			$file['message']	= $result['message'] .PHP_EOL. $this->_getHelper()->__('Please contact Shop Administrator');
		}
		
		return $file;
	}

}