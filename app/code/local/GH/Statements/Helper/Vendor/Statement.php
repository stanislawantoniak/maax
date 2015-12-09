<?php
class GH_Statements_Helper_Vendor_Statement extends Mage_Core_Helper_Abstract {

	/**
	 * @param GH_Statements_Model_Statement $statement
	 * @throws Zend_Controller_Response_Exception
	 */
	public function downloadStatementPdf(GH_Statements_Model_Statement $statement) {
		$vendor = Mage::getModel("udropship/vendor")->load($statement->getVendorId());
		$pdfName = "Statement-" . $vendor->getVendorName() . '-' . $statement->getEventDate();
		$filename = preg_replace("/[^a-z0-9\._-]+/i", "-", trim($pdfName)) . '.pdf';
		$file = file_get_contents($this->getStatementPdf($statement)); //todo:check path

		Mage::app()->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Pragma', 'public', true)
			->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
			->setHeader ('Content-type', 'application/force-download', true )
			->setHeader('Content-Length', strlen($file)) //size in bytes
			->setHeader('Content-Disposition', 'inline;' . '; filename='.$filename);
		Mage::app()->getResponse()->clearBody();
		Mage::app()->getResponse()->sendHeaders();

		echo $file;
	}

	protected function getStatementPdf(GH_Statements_Model_Statement $statement) {
		if(!$statement->getStatementPdf() || !is_file(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$statement->getStatementPdf())) {
			$this->generateStatementPdf($statement);
		}
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$statement->getStatementPdf();
	}

	protected function formatQuota($value)
	{
		return number_format(Mage::app()->getLocale()->getNumber(floatval($value)), 2);
	}
	protected function generateStatementPdf(GH_Statements_Model_Statement &$statement) {
		$page1data = array(
			"name" => $statement->getName(),
			"title" => $this->__("Balance"),
			"statement" => array(
				$this->__("Payments for fulfilled orders") => $this->formatQuota($statement->getOrderValue()),
				$this->__("Payment refunds for returned orders") => $this->formatQuota($statement->getRefundValue()),
				$this->__("Modago commission") => $this->formatQuota($statement->getTotalCommission()),
				$this->__("Discounts covered by Modago") => $this->formatQuota($statement->getGalleryDiscountValue()),
				$this->__("Other manual commission credit/debit notes") => $this->formatQuota($statement->getCommissionCorrection()),
				$this->__("Carrier costs") => $this->formatQuota($statement->getTrackingChargeTotal()),
				$this->__("Manual carrier fees credit/debit notes") => $this->formatQuota($statement->getDeliveryCorrection()),
				$this->__("Marketing costs") => $this->formatQuota($statement->getMarketingValue()),
				$this->__("Manual marketing fees credit/debit notes") => $this->formatQuota($statement->getMarketingCorrection()),
				$this->__("To pay") => $this->formatQuota($statement->getToPay()),
			),
			"saldo" => array(
				$this->__("Previous statement balance") => $this->formatQuota($statement->getLastStatementBalance()),
				$this->__("Vendor payouts") => $this->formatQuota($statement->getPaymentValue()),
				$this->__("Current statement balance") => $this->formatQuota($statement->getActualBalance())
			)
		);

		$page2data = array(
			"title" => $this->__("Tracking"),
			"header" => array(
				$this->__("Order No."),
				$this->__("RMA No."),
				$this->__("Package type"),
				$this->__("Shipped date"),
				$this->__("Carrier"),
				$this->__("Client No."),
				$this->__("Tracking No."),
				$this->__("Package cost"),
				$this->__("COD and insurance cost"),
				$this->__("Fuel cost"),
				$this->__("Cost netto"),
				$this->__("Cost brutto")
			),
			"body" => array(),
			"footer" => array(
				0 => $this->__("Total"),
				10 => 0.00,
				11 => 0.00
			)
		);

		$page3data = array(
			"title" => $this->__("Orders"),
			"header" => array(
				$this->__("Order No."),
				$this->__("Order/RMA Date"),
				$this->__("RMA No."),
				$this->__("Operation type"),
				$this->__("Operation date"),
				$this->__("Order/RMA Realization time"),
				$this->__("Payment method"),
				$this->__("Sale value (PLN)"),
				$this->__("To pay (PLN)")
			),
			"body" => array(),
			"footer" => array(
				0 => $this->__("Total"),
				7 => 0.00,
				8 => 0.00
			)
		);

		$page4data = array(
			"title" => $this->__("Commission"),
			"header" => array(
				$this->__("Order No."),
				$this->__("RMA No."),
				$this->__("Date of shipment/RMA closure"),
				$this->__("Transaction type"),
				$this->__("Product"),
				$this->__("SKU"),
				$this->__("Price before discount"),
				$this->__("Vendor discount"),
				$this->__("Modago discount"),
				$this->__("Price after discounts (PLN)"),
				$this->__("Modago commission rate (%)"),
				$this->__("Modago commission (PLN)")
			),
			"body" => array(),
			"footer" => array(
				0 => $this->__("Total"),
				6 => 0.00,
				7 => 0.00,
				8 => 0.00,
				9 => 0.00,
				11 => 0.00
			)
		);


		/** @var GH_Statements_Model_Track $tracksModel */
		$tracksModel = Mage::getModel("ghstatements/track");
		/** @var GH_Statements_Model_Resource_Track_Collection $tracksCollection */
		$tracksCollection = $tracksModel->getCollection();
		$tracksCollection
			->addFieldToFilter("statement_id",$statement->getId());

		if($tracksCollection->getSize()) {
			$packageTypes = array(
				GH_Statements_Model_Track::TRACK_TYPE_UNDELIVERED => $this->__("Undelivered package"),
				GH_Statements_Model_Track::TRACK_TYPE_ORDER => $this->__("Order shipment"),
				GH_Statements_Model_Track::TRACK_TYPE_RMA_CLIENT => $this->__("Goods return"),
				GH_Statements_Model_Track::TRACK_TYPE_RMA_VENDOR => $this->__("RMA shipment")
			);

			foreach ($tracksCollection as $track) {
				$page2data["footer"][10] += floatval($track->getChargeSubtotal());
				$page2data["footer"][11] += floatval($track->getChargeTotal());
				$page2data["body"][] = array(
					$track->getPoIncrementId(),
					$track->getRmaIncrementId(),
					$packageTypes[$track->getTrackType()],
					$track->getShippedDate(),
					$track->getTitle(),
					$track->getShippingSourceAccount(),
					$track->getTrackNumber(),
					$this->formatQuota($track->getChargeShipment()),
					$this->formatQuota(floatval($track->getChargeInsurance()) + floatval($track->getChargeCod())),
					$this->formatQuota($track->getChargeFuel()),
					$this->formatQuota($track->getChargeSubtotal()),
					$this->formatQuota($track->getChargeTotal())
				);
			}
		}

		/** @var GH_Statements_Model_Order $orderModel */
		$orderModel = Mage::getModel('ghstatements/order');
		/** @var GH_Statements_Model_Resource_Order_Collection $orderCollection */
		$orderCollection = $orderModel->getCollection();
		$orderCollection->addFieldToFilter("statement_id",$statement->getId());

		/** @var GH_Statements_Model_Rma $rmaModel */
		$rmaModel = Mage::getModel('ghstatements/rma');
		/** @var GH_Statements_Model_Resource_Rma_Collection $rmaCollection */
		$rmaCollection = $rmaModel->getCollection();
		$rmaCollection->addFieldToFilter("statement_id",$statement->getId());

		$page3body = array();
		$page4body = array();
		if($orderCollection->getSize() || $rmaCollection->getSize()) {


			if($orderCollection->getSize()) {
				$pos = array();
				foreach($orderCollection as $order) {
					$poId = $order->getPoId();
					$poIncrementId = $order->getPoIncrementId();
					$po = isset($pos[$poId]) ?
						$pos[$poId] :
						$pos[$poId] = Mage::getModel("udropship/po")->load($poId);

					//fill 3rd page start
					$currentFinalPrice = floatval($order->getFinalPrice()) + floatval($order->getShippingCost());
					if(!isset($page3body[$poIncrementId])) {
						$page3body[$poIncrementId] = array(
							$poIncrementId,
							date("Y-m-d", strtotime($po->getCreatedAt())),
							"",
							$this->__("Order shipment"),
							$order->getShippedDate(),
							"",//todo: realization time
							$this->__($order->getPaymentMethod()),
							$currentFinalPrice,
							$order->getPaymentChannelOwner() ? $currentFinalPrice : 0,
						);
					} else {
						$page3body[$poIncrementId][7] += $currentFinalPrice;
						if($order->getPaymentChannelOwner()) {
							$page3body[$poIncrementId][8] += $currentFinalPrice;
						}
					}
					$page3data["footer"][7] += $currentFinalPrice;
					if($order->getPaymentChannelOwner()) {
						$page3data["footer"][8] += $currentFinalPrice;
					}
					//fill 3rd page end

					//fill 4th page start
					$orderId = $order->getId();

					$product = Mage::getModel('catalog/product')->loadByAttribute('skuv',$order->getSku());
					$page4body[$orderId] = array(
						$poIncrementId,
						"",
						$order->getShippedDate(),
						$this->__("Sell"),
						$product->getName(),
						$order->getSku(),
						$this->formatQuota($order->getPrice()),
						$this->formatQuota($order->getDiscountAmount()),
						$this->formatQuota($order->getGalleryDiscountValue()),
						$this->formatQuota($order->getFinalPrice()),
						round($order->getCommissionPercent(),2),
						$this->formatQuota($order->getCommissionValue())
					);
					$page4data["footer"][6] += floatval($order->getPrice());
					$page4data["footer"][7] += floatval($order->getDiscountAmount());
					$page4data["footer"][8] += floatval($order->getGalleryDiscountValue());
					$page4data["footer"][9] += floatval($order->getFinalPrice());
					$page4data["footer"][11] +=floatval($order->getCommissionValue());
					//fill 4th page end
				}
			}

			if($rmaCollection->getSize()) {
				$rmas = array();
				foreach($rmaCollection as $rma) {
					/** @var GH_Statements_Model_Rma $rma */
					$rmaId = $rma->getRmaId();
					$poId = $rma->getPoId();
					$rmaIncrementId = $rma->getIncrementId();
					/** @var Zolago_Rma_Model_Rma $rmaModel */
					$rmaModel = isset($rmas[$rmaId]) ?
						$rmas[$rmaId] :
						$rmas[$rmaId] = Mage::getModel("urma/rma")->load($rmaId);

					//fill 3rd page start
					$currentFinalPrice = -floatval($rma->getApprovedRefundAmount());
					if(!isset($page3body[$rmaIncrementId])) {
						$page3body[$rmaIncrementId] = array(
							$rma->getPoIncrementId(),
							date("Y-m-d", strtotime($rmaModel->getCreatedAt())),
							$rmaIncrementId,
							$this->__("Order shipment"),
							$rma->getCarrierDate(), //todo: which date should be here?
							"",//todo: realization time
							$this->__($rma->getPaymentMethod()),
							$this->formatQuota($currentFinalPrice),
							$rma->getPaymentChannelOwner() ? $currentFinalPrice : 0
						);
					} else {
						$page3body[$rmaIncrementId][7] += $currentFinalPrice;
						if($rma->getPaymentChannelOwner()) {
							$page3body[$rmaIncrementId][8] += $currentFinalPrice;
						}
					}

					$page3data["footer"][7] += $currentFinalPrice;
					if($rma->getPaymentChannelOwner()) {
						$page3data["footer"][8] += $currentFinalPrice;
					}
					//fill 3rd page end

					//fill 4th page start
					$rmaId = "rma_".$rma->getId();
					$product = Mage::getModel('catalog/product')->loadByAttribute('skuv',$rma->getSku());
					$page4body[$rmaId] = array(
						$rmaIncrementId,
						"",
						$rma->getEventDate(),
						$this->__("Return"),
						$product->getName(),
						$rma->getSku(),
						$this->formatQuota(floatval(-$rma->getPrice())),
						$this->formatQuota($rma->getDiscountAmount()),
						$this->formatQuota(floatval(-$rma->getGalleryDiscountValue())),
						$this->formatQuota(floatval(-$rma->getApprovedRefundAmount())),
						round($rma->getCommissionPercent(),2),
						$this->formatQuota(floatval(-$rma->getCommissionValue()))
					);
					$page4data["footer"][6] += floatval(-$rma->getPrice());
					$page4data["footer"][7] += floatval($order->getDiscountAmount());
					$page4data["footer"][8] += floatval(-$rma->getGalleryDiscountValue());
					$page4data["footer"][9] += floatval(-$rma->getApprovedRefundAmount());
					$page4data["footer"][11] +=floatval(-$rma->getCommissionValue());
					//fill 4th page end
				}
			}
		}

		// format quota for specific fields in pagedata
		// page 2
    	$page2data["footer"][10] = $this->formatQuota($page2data["footer"][10]);
    	$page2data["footer"][11] = $this->formatQuota($page2data["footer"][11]);
    	// page 3
    	foreach ($page3body as $key=>$row) {
            $page3body[$key][7] = $this->formatQuota($page3body[$key][7]);    	    
            $page3body[$key][8] = $this->formatQuota($page3body[$key][8]);    	    
    	}
    	$page3data['footer'][7] = $this->formatQuota($page3data['footer'][7]);
    	$page3data['footer'][8] = $this->formatQuota($page3data['footer'][8]);
    	// page 4
    	$page4data['footer'][6] = $this->formatQuota($page4data['footer'][6]);
    	$page4data['footer'][7] = $this->formatQuota($page4data['footer'][7]);
    	$page4data['footer'][8] = $this->formatQuota($page4data['footer'][8]);
    	$page4data['footer'][9] = $this->formatQuota($page4data['footer'][9]);
    	$page4data['footer'][11] = $this->formatQuota($page4data['footer'][11]);
    	
		$page3data["body"] = $page3body;
		$page4data["body"] = $page4body;
		
    	
		
		/** @var GH_Statements_Model_Vendor_Pdf $pdfModel */
		$pdfModel = Mage::getModel('ghstatements/vendor_pdf');
		$pdfModel->generatePage1Html($page1data);
		$pdfModel->generatePage2Html($page2data);
		$pdfModel->generatePage3Html($page3data);
		$pdfModel->generatePage4Html($page4data);
		$pdfModel->setVariables($statement);

		$pdfModel->getPdfFile($statement);
	}
}