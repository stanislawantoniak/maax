<?php
/**
 * lp master controller
 */
abstract class Orba_Shipping_Controller_Lp extends Zolago_Dropship_Controller_Vendor_Abstract
{
    protected $_helper;
    public function indexAction() {
        $this->_redirect('/');
    }

    abstract protected function _getHelper();
    abstract protected function _getCarrierCode();
    abstract protected function _getLpDownloadUrl();
    abstract protected function _getSettings();
    abstract protected function _getCode();

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
                $trackNumber = $request->getParam('trackNumber'); // new track number if changed
                if (!empty($webCall['status'])) {
                    
                    $file = $helper->getIsFileAvailable($trackNumber);
                    
                } else {
                    $result['message'] = $webCall['message'];
                }
            }
        }
        // replace track number
        $params = $request->getParams();
        $params['trackNumber'] = $trackNumber;
        $params['_secure'] = true;
        $url = Mage::helper('orbashipping')->getShippingManager($this->_getCode())->getLetterUrl();
        $linkUrl = Mage::getUrl($url, $params);
        if ($file) {
            $result['status']	= true;
            $result['file']		= $trackNumber;
            $result['url']		= Mage::getUrl($this->_getLpDownloadUrl(), array("fileName"=>$trackNumber,'_secure'=>true));
            $result['linkUrl'] = $linkUrl;
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
        $file = array (
            'status' => false,
            'message' => false,
            'file' => false,
        );
        try {
            $vendorId	= $request->getParam('vId');
            $posId		= $request->getParam('posId');
            $trackId	= $request->getParam('trackId');
            $udpoId		= $request->getParam('udpoId');

            $vendorModel	= Mage::getModel('udropship/vendor')->load($vendorId);
            $trackModel		= Mage::getModel('sales/order_shipment_track')->load($trackId);
            $udpoModel		= Mage::getModel('udpo/po')->load($udpoId);

            $settings = $this->_getSettings();
            $client		= $this->_getHelper()->startClient($settings);
            $client->setShipmentSettings($settings);
            $file 		= $client->getLabelFile($trackModel);
            if (!$file['status']) {
                $this->_getHelper()->addUdpoComment($udpoModel, $file['message'], false, true, false);
                $file['message'] .= PHP_EOL. $this->_getHelper()->__('Please contact Shop Administrator');
            }
        } catch (Exception $xt) {
            Mage::logException($xt);
            $file['message'] = $xt->getMessage().PHP_EOL. $this->_getHelper()->__('Please contact Shop Administrator');
        }
        return $file;
    }

}