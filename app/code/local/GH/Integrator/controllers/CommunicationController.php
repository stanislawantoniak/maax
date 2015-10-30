<?php

class GH_Integrator_CommunicationController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		$data = $this->getRequest();

		$vendorId = isset($data['external_id']) ? $data['external_id'] : null;
		$secret = isset($data['secret']) ? $data['secret'] : null;

		/** @var GH_Integrator_Helper_Data $helper */
		$helper = Mage::helper('ghintegrator');

		try {
			if (!$vendorId || !$secret) {
				$helper->throwException(
					"Incorrect input data provided! \n external_id: " .
					print_r($vendorId) . "\n secret: " . print_r($secret)
				);
			}

			/** @var Zolago_Dropship_Model_Vendor $vendor */
			$vendor = Mage::getModel('udropship/vendor')->load($vendorId);
			if (!$vendor->getId()) {
				$helper->throwException("Provided external id is not valid! (" . print_r($vendorId) . ")");
			}

			if (!$vendor->getIntegratorEnabled()) {
				$helper->throwException("Integrator is disabled for vendor with ID: {$vendorId}", $vendorId);
			}

			if ($data['secret'] !== $vendor->getIntegratorSecret()) {
				$helper->throwException("Provided secret is not valid for this vendor! (" . print_r($secret) . ")", $vendorId);
			}


			//load config values
			$descriptionTimes = $helper->getDescriptionTimes();
			if(!count($descriptionTimes)) {
				$helper->throwException("GH Integrator description hours are incorrect!");
			}

			$priceTimes = $helper->getPriceTimes();
			if(!count($priceTimes)) {
				$helper->throwException("GH Integrator price hours are incorrect!");
			}

			$stockTimes = $helper->getStockTimes();
			if(!count($stockTimes)) {
				$helper->throwException("GH Integrator stock hours are incorrect!");
			}

			//everything is checked let's continue
			$filesToUpdate = array();
			$lastIntegrationTime = $vendor->getLastIntegration() ? strtotime($vendor->getLastIntegration()) : 0;
			$currentTime = Mage::getModel('core/date')->timestamp(time());

			echo $this->returnResponse($helper::STATUS_OK,$filesToUpdate);
		} catch(GH_Integrator_Exception $exception) {
			$helper->log($exception->getMessage(),$vendorId);
			echo $this->returnResponse($helper::STATUS_ERROR);
		} catch(Exception $exception) {
			$helper->log("Other error occured! See exception.log for details");
			Mage::logException($exception);
			echo $this->returnResponse($helper::STATUS_FATAL_ERROR);
		}
		return;
	}

	/**
	 * @param string $status
	 * @param array $files
	 * @return string
	 */
	protected function returnResponse($status,$files=null) {
		$response = array('status' => $status);
		if(is_array($files) && count($files)) {
			$response['files'] = $files;
		}
		return json_encode($response);
	}
}