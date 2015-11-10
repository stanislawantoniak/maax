<?php

class GH_Integrator_CommunicationController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $data = $this->getRequest()->getParams();

        $vendorId = isset($data['external_id']) ? $data['external_id'] : null;
        $secret = isset($data['secret']) ? $data['secret'] : null;

        /** @var GH_Integrator_Helper_Data $helper */
        $helper = Mage::helper('ghintegrator');

        try {
            if (!$vendorId || !$secret) {
                $helper->throwException("Incorrect input data provided!\n external_id: $vendorId\n secret: $secret");
            }

            /** @var Zolago_Dropship_Model_Vendor $vendor */
            $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
            if (!$vendor->getId()) {
                $helper->throwException("Provided external id is not valid! ($vendorId)");
            }

            if (!$vendor->getIntegratorEnabled()) {
                $helper->throwException("Integrator is disabled for vendor with ID: $vendorId", $vendorId);
            }

            if ($secret !== $vendor->getIntegratorSecret()) {
                $helper->throwException("Provided secret is not valid for this vendor! ($secret)", $vendorId);
            }


            //load config values
            $descriptionTimes = $helper->getDescriptionTimes();
            if (!count($descriptionTimes)) {
                $helper->throwException("GH Integrator description hours are incorrect!");
            }

            $priceTimes = $helper->getPriceTimes();
            if (!count($priceTimes)) {
                $helper->throwException("GH Integrator price hours are incorrect!");
            }

            $stockTimes = $helper->getStockTimes();
            if (!count($stockTimes)) {
                $helper->throwException("GH Integrator stock hours are incorrect!");
            }

            //everything is checked let's continue
            $filesToUpdate = array();
            $lastIntegrationTime = $vendor->getLastIntegration() ? strtotime($vendor->getLastIntegration()) : 0;
            $currentTime = Mage::getModel('core/date')->timestamp();

            //check if descriptions are candidate to update
            foreach ($descriptionTimes as $descriptionTime) { //array is sorted from latest to earliest hour
                if ($descriptionTime <= $currentTime && $descriptionTime > $lastIntegrationTime) {
                    $filesToUpdate[] = $helper::FILE_DESCRIPTIONS;
                    break;
                }
            }

            //check if prices are candidate to update
            foreach ($priceTimes as $priceTime) { //array is sorted from latest to earliest hour
                if ($priceTime <= $currentTime && $priceTime > $lastIntegrationTime) {
                    $filesToUpdate[] = $helper::FILE_PRICES;
                    break;
                }
            }

            //check if stocks are candidate to update
            foreach ($stockTimes as $stockTime) { //array is sorted from latest to earliest hour
                if ($stockTime <= $currentTime && $stockTime > $lastIntegrationTime) {
                    $filesToUpdate[] = $helper::FILE_STOCKS;
                    break;
                }
            }

            /** @var Mage_Core_Model_Date $dateModel */
            $dateModel = Mage::getModel('core/date');
            $vendor
                ->setLastIntegration($dateModel->timestamp())
                ->save();

            echo $this->returnResponse($helper::STATUS_OK, $vendor, $filesToUpdate);
        } catch (GH_Integrator_Exception $exception) {
            $helper->log($exception->getMessage(), $vendorId);
            echo $this->returnResponse($helper::STATUS_ERROR);
        } catch (Exception $exception) {
            $helper->log("Other error occurred! " . $exception->getMessage() . "\n<br/> See exception.log for more details");
            Mage::logException($exception);
            echo $this->returnResponse($helper::STATUS_FATAL_ERROR);
        }
        return;
    }


    /**
     * Prepare response for vendor
     * Response contains:
     * 'status' @see GH_Integrator_Helper_Data
     * if there is aby file to generate
     * 'files' and 'ftp_url'
     *
     * @param string $status
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param array $files
     * @return string
     */
    protected function returnResponse($status, $vendor = null, $files = null)
    {
        $response = array('status' => $status);
        if (is_array($files) && count($files)) {
            $response['files'] = $files;
            $response['ftp_url'] = $this->getFtpUrl($vendor);
        }
        return json_encode($response);
    }

    /**
     * Prepare url for upload file to Modago FTP
     *
     * @param $vendor
     * @return string
     */
    protected function getFtpUrl($vendor)
    {
        $login = $this->getFtpLogin($vendor);
        $passwd = $this->getFtpPasswd($vendor);
        $host = $this->getFtpHost($vendor);
        return "ftp://{$login}:{$passwd}@{$host}/";
    }

    /**
     * Get FTP login
     *
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @return string
     */
    protected function getFtpLogin($vendor)
    {
        return Mage::getStoreConfig("ghintegrator/ftp/login");
    }

    /**
     * Get FTP password
     *
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @return string
     */
    protected function getFtpPasswd($vendor)
    {
        return Mage::getStoreConfig("ghintegrator/ftp/password");
    }

    /**
     * Get FTP host
     *
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @return string
     */
    protected function getFtpHost($vendor)
    {
        return Mage::getStoreConfig("ghintegrator/ftp/host");
    }
}