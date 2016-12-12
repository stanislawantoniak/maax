<?php
/**
 * carrier module helper
 */
class Orba_Shipping_Helper_Carrier extends Mage_Core_Helper_Abstract {

    const USER_NAME_COMMENT		= 'API';

    protected $_fileDir;

    /**
     * @var tracking helper
     */
    protected $_trackingHelper = null;
    /**
     * Special Log Message Function
     *
     * @param string $message	Message to Log
     * @param string $logFile	Log file name. Default: dhl_tracking.log
     */
    public function _log($message, $logFile = false) {
        if (!$logFile) {
            $logFile = $this->_logFile;
        }

        Mage::log($message, null, $logFile, true);
    }
	
    /**
     * setting specify helper for tracking
     * @param Orba_Shipping_Helper_Carrier_Tracking $helper
     * @return void
     */
    public function setTrackingHelper($helper) {
        $this->_trackingHelper = $helper;
    }

	/**
	 * @param bool $settings
	 * @return null
	 */
    public function startClient($settings = false) {
        // abstract function
        return null;
    }

    /**
     * check if carrier client is active
     * @return bool
     */
    public function isActive() {
        return false; //abstract
    }
    public function getFileExt() {
        return static::FILE_EXT;
    }

    public function addUdpoComment($udpo, $comment, $isVendorNotified=false, $visibleToVendor=false, $userName = false)
    {
        if (!$userName) {
            $userName = self::USER_NAME_COMMENT;
        }
        $commentModel = Mage::getModel('udpo/po_comment')
                        ->setParentId($udpo->getId())
                        ->setComment($comment)
                        ->setCreatedAt(now())
                        ->setIsVendorNotified($isVendorNotified)
                        ->setIsVisibleToVendor($visibleToVendor)
                        ->setUdropshipStatus(Mage::helper("udpo")->getUdpoStatusName($udpo))
                        ->setUsername($userName);
        $commentModel->save();
    }

    public function getFileDir()
    {
        if ($this->_fileDir === null) {
            $this->_fileDir = $this->setFileDir();
        }

        return $this->_fileDir;
    }

    public function setFileDir()
    {
        if ($this->_fileDir === null) {
            $ioAdapter = new Varien_Io_File();
            $this->_fileDir = Mage::getBaseDir('media') . DS . static::FILE_DIR . DS;
            $ioAdapter->checkAndCreateFolder($this->_fileDir);
        }

        return $this->_fileDir;
    }


    public function getIsFileAvailable($trackNumber)
    {
        $file = false;
        if ($this->_fileDir === null) {
            $this->setFileDir();
            $file = $this->getIsFileAvailable($trackNumber);
        } else {
            $this->setFileDir();
            if (count($trackNumber)) {
                $ioAdapter = new Varien_Io_File();
                $fileLocation = $this->_fileDir . $trackNumber . '.' . $this->getFileExt();
                if ($ioAdapter->fileExists($fileLocation)) {
                    $file = $fileLocation;
                }
            }
        }
        return $file;
    }

    /**
     * calculate time of next check status
     */
    protected function _getNextCheck($storeId) {
        $repeatIn = Mage::getStoreConfig('carriers/orbaups/repeat_tracking', $storeId);
        if ($repeatIn <= 0) {
            $repeatIn = 1;
        }
        $repeatIn = $repeatIn*60*60;
        $time = Mage::getModel('core/date')->timestamp();
        return date('Y-m-d H:i:s', $time+$repeatIn);
    }
    /**
     * process track status
     */
    protected function _processTrackStatus($track,$result) {
        $message = array();
        $status			= $this->__('Ready to Ship');
        $shipmentIdMessage = '';
        $shipment		= $track->getShipment();
        $oldStatus = $track->getUdropshipStatus();
        try {
            $this->_parseTrackResponse($track,$result,$message,$status,$shipmentIdMessage);
            if ($oldStatus != $track->getUdropshipStatus()) {
                $this->_trackingHelper->addComment($track,$shipmentIdMessage,$message,$status);
            }
            if (!in_array($status, array($this->__('Delivered'), $this->__('Returned'), $this->__('Canceled')))) {
                $track->setNextCheck($this->_getNextCheck($shipment->getOrder()->getStoreId()));
            }
            $track->setWebApi(true);
            $track->save();
            $track->getShipment()->save();
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        return true;


    }


}