<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_MainController extends Mage_Core_Controller_Front_Action
{
    public function downloadAction()
    {
        $fileName = $this->getRequest()->getParam('file');
        $download = Mage::helper('amfeed')->getDownloadPath('feeds', $fileName);
        $this->_prepareDownloadResponse($fileName, file_get_contents($download));
    }
       
}

?>