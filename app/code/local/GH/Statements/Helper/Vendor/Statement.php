<?php
class GH_Statements_Helper_Vendor_Statement extends Mage_Core_Helper_Abstract {

    /**
     * List of Zolago_Po_Model_Po
     * Used by getPoById()
     * @var array
     */
    protected $pos = array();

	/**
     * List of Zolago_Rma_Model_Rma
     * used by getRmaById()
     * @var array
     */
    protected $rmas = array();

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
		return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).$statement->getStatementPdf();
	}

	protected function formatQuota($value)
	{
		return number_format(Mage::app()->getLocale()->getNumber(floatval($value)), 2);
	}
	protected function generateStatementPdf(GH_Statements_Model_Statement &$statement) {

	    $headerText = sprintf('%s<br/>%s',
	        Mage::getStoreConfig('general/store_information/name',Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID),
	        Mage::getStoreConfig('general/store_information/address',Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID));
	        
	    $eventDate = date('Y-m-d',strtotime($statement->getEventDate()) + 3600*24);
	    $vendor = Mage::getModel('udropship/vendor')->load($statement->getVendorId());
	    $vendorData = sprintf('%s, %s (NIP:%s)',$vendor->getCompanyName(),$vendor->getBillingAddress(),$vendor->getTaxNo());
	    if ($statement->getDateFrom()) {
	        $periodText = sprintf('%s - %s',date("Y-m-d",strtotime($statement->getDateFrom())),date('Y-m-d',strtotime($statement->getEventDate())));	        
	    } else {
	        $periodText = $this->__('to %s',$statement->getEventDate());
	    }
	    $nameText = $this->__("MODAGO financial statement on %s for period %s <br/>issued for %s",$eventDate,$periodText,$vendorData);
	    $footerData = array (
    	    'name' => $this->__("MODAGO financial statement on %s",$eventDate),
        );
        $dateFrom = $statement->getDateFrom();
	    $lastStatementData = empty($dateFrom)? '':sprintf(' (%s)',date('Y-m-d',strtotime($statement->getDateFrom())));
		$page1data = array(
		    "header" => $headerText,
			"name" => $nameText, // $statement->getName(),
			"title" => $this->__("Balance"),
			"statement" => array(),
			"saldo" => array(
				$this->__("[B] Previous statement balance%s",$lastStatementData) => $statement->getLastStatementBalance(),
				$this->__("[C] Vendor payouts") => $statement->getPaymentValue(),
				$this->__("Current statement balance [B]+[A]-[C]") => $statement->getActualBalance()
			)
		);

		$page1data['statement'][$this->__("[1] Payments for fulfilled orders")] = $statement->getOrderValue();
		$page1data['statement'][$this->__("[2] Payment refunds for returned orders")] = $statement->getRefundValue();
		$page1data['statement'][$this->__("[3] Modago commission")] = $statement->getTotalCommission();
		$page1data['statement'][$this->__("[4] Discounts covered by Modago")] = $statement->getGalleryDiscountValue();
		$step = 5;
		$calculateMethod = '[1]-[2]-[3]+[4]';
		if ($statement->getCommissionCorrection() != 0) {
    		$page1data['statement'][$this->__("[5] Other manual commission credit/debit notes")] = $statement->getCommissionCorrection();
    		$calculateMethod .= '+[5]';
    		$step++;
        }
        $calculateMethod .= sprintf('-[%d]',$step);
		$page1data['statement'][$this->__("[%d] Carrier costs",$step++)] = $statement->getTrackingChargeTotal();
		if ($statement->getDeliveryCorrection()!= 0) {
            $calculateMethod .= sprintf('+[%d]',$step);
    		$page1data['statement'][$this->__("[%d] Manual carrier fees credit/debit notes",$step++)] = $statement->getDeliveryCorrection();
        }
        if ($statement->getMarketingValue() != 0) {
            $calculateMethod .= sprintf('-[%d]',$step);        
    		$page1data['statement'][$this->__("[%d] Marketing costs",$step++)] = $statement->getMarketingValue();
        }
        if ($statement->getMarketingCorrection() != 0) {
            $calculateMethod .= sprintf('+[%d]',$step);        
    		$page1data['statement'][$this->__("[%d] Manual marketing fees credit/debit notes",$step++)] = $statement->getMarketingCorrection();
        }
		$page1data['topay'] = array (
		    'title' => $this->__("[A] To pay %s",$calculateMethod),
		    'value' => $statement->getToPay()
        );


        //$page1data = $this->_initPage1Ddata(); //todo
        $page2data = $this->_initPage2Ddata();
        $page3data = $this->_initPage3Ddata();
        $page4data = $this->_initPage4Ddata();

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
					$track->getShippedDate(), // todo zamienic
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

        $page3body = array();
        $page4body = array();

        /** @var GH_Statements_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::getResourceModel("ghstatements/order_collection");
        $orderCollection->addFieldToFilter("statement_id",$statement->getId());

        if ($orderCollection->count()) {
            $this->_fillPage3byOrders($orderCollection, $page3data, $page3body);
            $this->_fillPage4byCommission($orderCollection, $page4data, $page4body);
        }

        /** @var GH_Statements_Model_Resource_Refund_Collection $refundsCollection */
        $refundsCollection = Mage::getResourceModel("ghstatements/refund_collection");
        $refundsCollection->addFieldToFilter("statement_id", $statement->getId());

        if ($refundsCollection->count()) {
            $this->_fillPage3byRefunds($refundsCollection, $page3data, $page3body);
        }

        /** @var GH_Statements_Model_Resource_Rma_Collection $rmaCollection */
        $rmaCollection = Mage::getResourceModel("ghstatements/rma_collection");
        $rmaCollection->addFieldToFilter("statement_id", $statement->getId());

        if ($rmaCollection->count()) {
            $this->_fillPage4byRma($rmaCollection, $page4data, $page4body);
        }


		// format quota for specific fields in pagedata
		// page 2
    	$page2data["footer"][10] = $this->formatQuota($page2data["footer"][10]);
    	$page2data["footer"][11] = $this->formatQuota($page2data["footer"][11]);
    	// page 3
    	foreach ($page3body as $key=>$row) {
            $page3body[$key][6] = $this->formatQuota($page3body[$key][6]);
            $page3body[$key][7] = $this->formatQuota($page3body[$key][7]);
    	}
    	$page3data['footer'][6] = $this->formatQuota($page3data['footer'][6]);
    	$page3data['footer'][7] = $this->formatQuota($page3data['footer'][7]);
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
		$pdfModel->generateFooter($footerData);
		$pdfModel->setVariables($statement);

		$pdfModel->getPdfFile($statement);
	}

    protected function _initPage1Ddata() {

    }

    /**
     * @return array
     */
    protected function _initPage2Ddata() {
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
        return $page2data;
    }

    /**
     * [0] Order No.
     * [1] Order/RMA Date
     * [2] RMA No.
     * [3] Operation type
     * [4] Operation date
     * [ ] Order/RMA Realization time
     * [5] Payment method
     * [6] Sale value (PLN)
     * [7] To pay (PLN)
     *
     * @return array
     */
    protected function _initPage3Ddata() {
        $page3data = array(
            "title" => $this->__("Orders shipped and returns for the accounting period"),
            "header" => array(
                $this->__("Order No."),
                $this->__("Order/RMA Date"),
                $this->__("RMA No."),
                $this->__("Operation type"),
                $this->__("Operation date"),
                //$this->__("Order/RMA Realization time"),
                $this->__("Payment method"),
                $this->__("Sale value (PLN)"),
                $this->__("To pay (PLN)")
            ),
            "body" => array(),
            "footer" => array(
                0 => $this->__("Total"),
                6 => 0.00,
                7 => 0.00
            )
        );
        return $page3data;
    }

    /**
     * [0] Order No.
     * [1] RMA No.
     * [2] Date of shipment/RMA closure
     * [3] Transaction type
     * [4] Product
     * [5] SKU"
     * [6] Price before discount
     * [7] Vendor discount
     * [8] Modago discount
     * [9] Price after discounts (PLN)
     * [10] Modago commission rate (%)
     * [11] Modago commission (PLN)
     * @return array
     */
    protected function _initPage4Ddata() {
        $page4data = array(
            "title" => $this->__("Commissions Modago for orders shipped and returned during the accounting period"),
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
        return $page4data;
    }

    /**
     * @param GH_Statements_Model_Resource_Order_Collection $orderCollection
     * @param $page3data
     * @param $page3body
     * @return $this
     */
    protected function _fillPage3byOrders($orderCollection, &$page3data, &$page3body) {
        /** @var Mage_Core_Model_Date $cd */
        $cd = Mage::getModel('core/date');

        foreach ($orderCollection as $order) {
            /** @var GH_Statements_Model_Order $order */
            $poId = $order->getPoId();
            $po   = $this->getPoById($poId);
            $_id = "order_" . $order->getPoIncrementId();
            $currentFinalPrice = floatval($order->getFinalPrice()) + floatval($order->getShippingCost());
            if (!isset($page3body[$_id])) {
                $page3body[$_id] = array(
                    $order->getPoIncrementId(),                                // [0] Order No.
                    date("Y-m-d", $cd->timestamp($po->getCreatedAt())),        // [1] Order/RMA Date
                    "",                                                        // [2] RMA No.
                    $this->__("Order shipment"),                               // [3] Operation type
                    date("Y-m-d", $cd->timestamp($order->getShippedDate())),   // [4] Operation date
                    //"",                                                      // [ ] Order/RMA Realization time
                    $this->__($order->getPaymentMethod()),                     // [5] Payment method
                    $currentFinalPrice,                                        // [6] Sale value (PLN)
                    $order->getPaymentChannelOwner() ? $currentFinalPrice : 0, // [7] To pay (PLN)
                );
            } else {
                // Sum of po items value
                $page3body[$_id][6] += $currentFinalPrice;
                if ($order->getPaymentChannelOwner()) {
                    $page3body[$_id][7] += $currentFinalPrice;
                }
            }
            $page3data["footer"][6] += $currentFinalPrice;
            if ($order->getPaymentChannelOwner()) {
                $page3data["footer"][7] += $currentFinalPrice;
            }
        }
        return $this;
    }

    /**
     * @param GH_Statements_Model_Resource_Order_Collection $orderCollection
     * @param $page4data
     * @param $page4body
     * @return $this
     */
    protected function _fillPage4byCommission($orderCollection, &$page4data, &$page4body) {
        /** @var Mage_Core_Model_Date $cd */
        $cd = Mage::getModel('core/date');

        foreach ($orderCollection as $order) {
            /** @var GH_Statements_Model_Order $order */
            /** @var Zolago_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->loadByAttribute('skuv', $order->getSku());
            $_id = "order_" . $order->getPoIncrementId();
            $page4body[$_id] = array(
                $order->getPoIncrementId(),                                                                              // [0] Order No.
                "",                                                                                                      // [1] RMA No.
                date("Y-m-d", $cd->timestamp($order->getShippedDate())),                                                 // [2] Date of shipment/RMA closure
                $this->__("Sale"),                                                                                       // [3] Transaction type
                $product->getName(),                                                                                     // [4] Product
                $order->getSku(),                                                                                        // [5] SKU"
                $this->formatQuota($order->getPrice()),                                                                  // [6] Price before discount
                $this->formatQuota(floatval($order->getDiscountAmount()) - floatval($order->getGalleryDiscountValue())), // [7] Vendor discount (Rabat udzielany przez partnera (discount_amount to suma rabatow vendora i sprzedawcy))
                $this->formatQuota($order->getGalleryDiscountValue()),                                                   // [8] Modago discount
                $this->formatQuota($order->getFinalPrice()),                                                             // [9] Price after discounts (PLN)
                $this->formatQuota(round($order->getCommissionPercent(), 2)),                                            // [10] Modago commission rate (%)
                $this->formatQuota($order->getCommissionValue())                                                         // [11] Modago commission (PLN)
            );
            $page4data["footer"][6] += floatval($order->getPrice());
            $page4data["footer"][7] += floatval($order->getDiscountAmount()) - floatval($order->getGalleryDiscountValue());
            $page4data["footer"][8] += floatval($order->getGalleryDiscountValue());
            $page4data["footer"][9] += floatval($order->getFinalPrice());
            $page4data["footer"][11] += floatval($order->getCommissionValue());
        }
        return $this;
    }

    /**
     * @param GH_Statements_Model_Resource_Refund_Collection $refundsCollection
     * @param $page3data
     * @param $page3body
     * @return $this
     */
    protected function _fillPage3byRefunds($refundsCollection, &$page3data, &$page3body) {
        /** @var Mage_Core_Model_Date $cd */
        $cd = Mage::getModel('core/date');

        foreach ($refundsCollection as $refund) {
            /** @var GH_Statements_Model_Refund $refund */
            $rmaId = $refund->getRmaId();
            $rmaIncrementId = $refund->getRmaIncrementId();
            $poIncrementId = $refund->getPoIncrementId();
            /** @var Zolago_Rma_Model_Rma $rma */
            $rma = $this->getRmaById($rmaId);
            /** @var Zolago_Po_Model_Po $po */
            $po  = $this->getPoById($refund->getPoId());

            $_id = 'refund_' . $refund->getId();
	        $registeredValue = $this->formatQuota(-floatval($refund->getRegisteredValue()));
            $value = $this->formatQuota(-floatval($refund->getValue()));
            $paymentMethod = $this->__(ucfirst(str_replace('_', ' ', $po->ghapiPaymentMethod())));

            $page3body[$_id] = array(
                $poIncrementId,                                      // [0] Order No.
                date("Y-m-d", $cd->timestamp($rma->getCreatedAt())), // [1] Order/RMA Date
                $rmaIncrementId,                                     // [2] RMA No.
                $this->__("Order return payment"),                   // [3] Operation type
                date("Y-m-d", $cd->timestamp($refund->getDate())),   // [4] Operation date
                //"",                                                // [ ] Order/RMA Realization time
                $paymentMethod,                                      // [5] Payment method
	            $registeredValue,                                    // [6] Sale value (PLN)
	            $value                                               // [7] To pay (PLN)
            );

            $page3data["footer"][6] += $registeredValue;
            $page3data["footer"][7] += $value;
        }
        return $this;
    }

    /**
     * @param GH_Statements_Model_Resource_Rma_Collection $rmaCollection
     * @param $page4data
     * @param $page4body
     * @return $this
     */
    protected function _fillPage4byRma($rmaCollection, &$page4data, &$page4body) {
        /** @var Mage_Core_Model_Date $cd */
        $cd = Mage::getModel('core/date');

        foreach ($rmaCollection as $ghRma) {
            /** @var GH_Statements_Model_Rma $ghRma */
            /** @var Zolago_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->loadByAttribute('skuv', $ghRma->getSku());
            $_id = 'rma' . $ghRma->getRmaIncrementId();
            $page4body[$_id] = array(
                $ghRma->getPoIncrementId(),                                                                               // [0] Order No.
                $ghRma->getRmaIncrementId(),                                                                              // [1] RMA No.
                date("Y-m-d", $cd->timestamp($ghRma->getEventDate())),                                                    // [2] Date of shipment/RMA closure
                $this->__("Return"),                                                                                      // [3] Transaction type
                $product->getName(),                                                                                      // [4] Product
                $ghRma->getSku(),                                                                                         // [5] SKU"
                $this->formatQuota(-floatval($ghRma->getApprovedRefundAmount())),                                         // [6] Price before discount
                $this->formatQuota(floatval($ghRma->getDiscountAmount()) - floatval($ghRma->getGalleryDiscountValue())),  // [7] Vendor discount
                $this->formatQuota(-floatval($ghRma->getDiscountReturn())),                                               // [8] Modago discount
                $this->formatQuota(-floatval($ghRma->getApprovedRefundAmount()) - floatval($ghRma->getDiscountReturn())), // [9] Price after discounts (PLN)
                $this->formatQuota(round($ghRma->getCommissionPercent(), 2)),                                             // [10] Modago commission rate (%)
                $this->formatQuota(-floatval($ghRma->getCommissionReturn()))                                              // [11] Modago commission (PLN)
            );
            $page4data["footer"][6] += (-floatval($ghRma->getApprovedRefundAmount()));
            $page4data["footer"][7] += (floatval($ghRma->getDiscountAmount()) - floatval($ghRma->getGalleryDiscountValue()));
            $page4data["footer"][8] += (-floatval($ghRma->getDiscountReturn()));
            $page4data["footer"][9] += (-floatval($ghRma->getApprovedRefundAmount()) - floatval($ghRma->getDiscountReturn()));
            $page4data["footer"][11] += (-floatval($ghRma->getCommissionReturn()));
        }
        return $this;
    }

    /**
     * @param $poId
     * @return Zolago_Po_Model_Po
     */
    protected function getPoById($poId) {
        return isset($this->pos[$poId]) ?
            $this->pos[$poId] :
            $this->pos[$poId] = Mage::getModel("zolagopo/po")->load($poId);
    }

    /**
     * @param $rmaId
     * @return Zolago_Rma_Model_Rma
     */
    protected function getRmaById($rmaId) {
        return isset($this->rmas[$rmaId]) ?
            $this->rmas[$rmaId] :
            $this->rmas[$rmaId] = Mage::getModel("urma/rma")->load($rmaId);
    }
}