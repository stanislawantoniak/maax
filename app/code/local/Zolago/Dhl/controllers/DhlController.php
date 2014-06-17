<?php
/**
 * dhl controller
 */
class Zolago_Dhl_DhlController extends Mage_Core_Controller_Front_Action
{	
	public function indexAction() {
		$this->_redirect('/');
	}
	
	public function lpAction() {
		$dhlFile			= false;
		$result	= array(
			'status'	=> false,
			'file'		=> false,
			'message'	=> Mage::helper('zolagodhl')->__('DHL Service Error')
		);
		$request = $this->getRequest();
		if ($request->getParam('trackNumber')) {
			$trackNumber = $request->getParam('trackNumber');
			$ioAdapter = new Varien_Io_File();
			$dhlFile = Mage::helper('zolagodhl')->getIsDhlFileAvailable($trackNumber);
			if (!$dhlFile) {
				$dhlWebCall = $this->_dhlLpCall($request);
				if ($dhlWebCall['status']) {
					$dhlFile = Mage::helper('zolagodhl')->getIsDhlFileAvailable($trackNumber);
				} else {
					$result['message'] = $dhlWebCall['message'];
				}
			}
		}

		if ($dhlFile) {
			$result['status']	= true;
			$result['file']		= $trackNumber;
			$result['url']		= Mage::getUrl('zolagodhl/dhl/lpDownload', array("dhlFileName"=>$trackNumber));
			unset($result['message']);
		}
		
		$resultJSON = Mage::helper('core')->jsonEncode($result);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($resultJSON);
	}
	
	public function lpDownloadAction() {
		$dhlFile = false;
		
		$request = $this->getRequest();
		if ($request->getParam('dhlFileName')) {
			$dhlFileName = $request->getParam('dhlFileName');
			$ioAdapter = new Varien_Io_File();
			$dhlFile = Mage::helper('zolagodhl')->getIsDhlFileAvailable($dhlFileName);
			if ($dhlFile) {
				return $this->_prepareDownloadResponse(basename($dhlFile), @$ioAdapter->read($dhlFile), 'application/pdf');
			}
		}
		$this->_redirectReferer();
	}	
	
	protected function _dhlLpCall($request)
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
		
		$dhlSettings	= Mage::helper('udpo')->getDhlSettings($vendorModel, $posId);
		$dhlClient		= Mage::helper('zolagodhl')->startDhlClient($dhlSettings);
		$dhlResult		= $dhlClient->getLabels($trackModel);
		$result			= $dhlClient->processDhlLabelsResult('getLabels', $dhlResult);
		
		if ($result['status']) {
			$ioAdapter			= new Varien_Io_File();
			$fileName			= $result['labelName'];
			$fileContent		= $result['labelData'];
			$fileLocation		= Mage::helper('zolagodhl')->getDhlFileDir() . $fileName;
			$dhlFile['status']	= true;
			$dhlFile['file']	= @$ioAdapter->filePutContent($fileLocation, $fileContent);
		} else {
			//Error Scenario
			Mage::helper('zolagodhl')->addUdpoComment($udpoModel, $result['message'], false, true, false);
			$dhlFile['message']	= $result['message'] .PHP_EOL. $this->__('Please contact Shop Administrator');
		}
		
		return $dhlFile;
	}

    public function checkDhlZipAction()
    {
        $zip = Mage::app()->getRequest()->getParam('postcode');
        $response = array();
        if (empty($zip)) {
            $response['status'] = 'error';
            $response['message'] = 'Please enter zip code';
            echo json_encode($response);
            return;
        }

        $dhlHelper = Mage::helper('zolagodhl');
        $dhlValidZip = $dhlHelper->isDHLValidZip($zip);

        if (!$dhlValidZip) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid Dhl Postal Code';
            echo json_encode($response);
            return;
        }

        $response['status'] = 'success';
        $response['message'] = 'Zip code is valid';
        echo json_encode($response);
        return;

    }
}