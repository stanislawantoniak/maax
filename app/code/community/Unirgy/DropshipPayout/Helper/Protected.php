<?php

class Unirgy_DropshipPayout_Helper_Protected
{
	public function payoutPay($payout) {
		Unirgy_Dropship_Helper_Protected::validateLicense("Unirgy_DropshipPayout");
		try {
			$ptHlp = Mage::helper("udpayout");
			if ($payout->getPayoutStatus() == Unirgy_DropshipPayout_Model_Payout::STATUS_PAID) {
				Mage::throwException($ptHlp->__("This payout already paid"));
			}

			if ($payout->getPayoutStatus() == Unirgy_DropshipPayout_Model_Payout::STATUS_CANCELED) {
				Mage::throwException($ptHlp->__("This payout is canceled"));
			}

			if ($payout->getPayoutStatus() == Unirgy_DropshipPayout_Model_Payout::STATUS_PAYPAL_IPN) {
				Mage::throwException($ptHlp->__("This payout wait paypal IPN"));
			}

			if ($payout->getTotalDue() <= 0) {
				Mage::throwException($ptHlp->__("Payout \"total due\" must be positive"));
			}

			if (!$payout->getPayoutMethod()) {
				Mage::throwException($ptHlp->__("Empty payout method"));
			}

			$pmNode = Mage::getConfig()->getNode("global/udropship/payout/method/" . $payout->getPayoutMethod());
			if (!$pmNode) {
				Mage::throwException($ptHlp->__("Unknown payout method: '%s'", $payout->getPayoutMethod()));
			}

			$methodClass = $pmNode->getClassName();
			if (!class_exists($methodClass)) {
				Mage::throwException($ptHlp->__("Can't find payout method class"));
			}

			$method = new $methodClass();
			$method->pay($payout);
			$payout->save();
		} catch (Exception $e) {
			$payout->addMessage($e->getMessage(), Unirgy_DropshipPayout_Model_Payout::STATUS_ERROR)->save();
			throw $e;
		}
	}

	public function sales_order_shipment_save_after($po) {
		Unirgy_Dropship_Helper_Protected::validateLicense("Unirgy_DropshipPayout");
		$vendor = Mage::helper("udropship")->getVendor($po->getUdropshipVendor());
		$ptPoStatuses = $vendor->getPayoutPoStatus();
		if (!is_array($ptPoStatuses)) {
			$ptPoStatuses = explode(",", $ptPoStatuses);
		}

		if ($vendor->getPayoutType() == "auto" && $vendor->getStatementPoType() == "shipment" && !$po->hasUdropshipPayoutStatus() && in_array($po->getUdropshipStatus(), $ptPoStatuses)) {
			try {
				Unirgy_DropshipPayout_Model_Payout::processPos(array($po), $vendor->getStatementSubtotalBase());
				$payout = Mage::helper("udpayout")->createPayout($vendor)->addPo($po)->finishPayout()->pay();
				$po->setUdropshipPayoutStatus($payout->getPayoutStatus());
				$po->getResource()->saveAttribute($po, "udropship_payout_status");
			} catch (Exception $e) {
				Mage::logException($e);
			}
		}
	}

	public function udpo_po_save_after($po) {
		Unirgy_Dropship_Helper_Protected::validateLicense("Unirgy_DropshipPayout");
		$vendor = Mage::helper("udropship")->getVendor($po->getUdropshipVendor());
		$ptPoStatuses = $vendor->getPayoutPoStatus();
		if (!is_array($ptPoStatuses)) {
			$ptPoStatuses = explode(",", $ptPoStatuses);
		}

		if ($vendor->getPayoutType() == "auto" && $vendor->getStatementPoType() == "po" && !$po->hasUdropshipPayoutStatus() && in_array($po->getUdropshipStatus(), $ptPoStatuses)) {
			try {
				Unirgy_DropshipPayout_Model_Payout::processPos(array($po), $vendor->getStatementSubtotalBase());
				$payout = Mage::helper("udpayout")->createPayout($vendor)->addPo($po)->finishPayout()->pay();
				$po->setUdropshipPayoutStatus($payout->getPayoutStatus());
				$po->getResource()->saveAttribute($po, "udropship_payout_status");
			} catch (Exception $e) {
				Mage::logException($e);
			}
		}
	}
}


