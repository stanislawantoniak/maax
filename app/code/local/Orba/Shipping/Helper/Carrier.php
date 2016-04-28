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
	
    //{{{ 
    /**
     * setting specify helper for tracking 
     * @param Orba_Shipping_Helper_Carrier_Tracking $helper
     * @return 
     */
     public function setTrackingHelper($helper) {
         $this->_trackingHelper = $helper;
     }
    //}}}
    //{{{ 
    /**
     * @param array $settings 
     * @return 
     */

    //}}}
	public function startClient($settings = false) {
	    // abstract function
        return null;	    
	}
	
	
    //{{{ 
    /**
     * check if carrier client is active
     * @return bool
     */
    public function isActive() {
        return false; //abstract
    }
    //}}}
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
                $fileLocation = $this->_fileDir . $trackNumber . '.' . static::FILE_EXT;
                if ($ioAdapter->fileExists($fileLocation)) {
                    $file = $fileLocation;
                }
            }
        }
        return $file;
    }
	


}