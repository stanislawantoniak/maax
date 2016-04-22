<?php
class Zolago_Dropship_Model_Pdf_Shipment extends ZolagoOs_OmniChannel_Model_Pdf_Shipment {

	protected function insertOrder(&$page, $order, $putOrderId = true)
    {
        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        /* @var $order Mage_Sales_Model_Order */
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));

        $page->drawRectangle(25, $top, 570, $top-55);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page);

        if (Mage::helper('udropship')->isUdpoActive()) {
            $po = Mage::helper('udpo')->getShipmentPo($this->_currentShipment);
        }

        if ($putOrderId) {
            $page->drawText(Mage::helper('sales')->__('Order # ').$order->getRealOrderId(), 35, ($top -= 10), 'UTF-8');
            if (!empty($po)) {
                $page->drawText(Mage::helper('udpo')->__('Purchase Order # ').$po->getIncrementId(), 135, $top, 'UTF-8');
            }
        }
        //$page->drawText(Mage::helper('sales')->__('Order Date: ') . date( 'D M j Y', strtotime( $order->getCreatedAt() ) ), 35, 760, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), 35, ($top -= 30), 'UTF-8');
        if (!empty($po)) {
            $page->drawText(Mage::helper('udpo')->__('Purchase Order Date: ') . Mage::helper('core')->formatDate($po->getCreatedAtStoreDate(), 'medium', false), 135, $top, 'UTF-8');
        }

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, ($top -= 5), 275, ($top-25));
        $page->drawRectangle(275, $top, 570, ($top-25));

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress(
            Mage::helper('udropship')->formatCustomerAddress(/*fix $order*/$po->getBillingAddress(), 'pdf', $this->_currentShipment->getUdropshipVendor())
        );

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value))==''){
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
			$shippingAddress = Mage::getModel("sales/order_address")->
					load($this->_currentShipment->getShippingAddressId());
			if(!$shippingAddress->getId()){
				$shippingAddress = $po->getShippingAddress();
			}
            $shippingAddress = $this->_formatAddress(
                Mage::helper('udropship')->formatCustomerAddress(/*fix $order*/$shippingAddress, 'pdf', $this->_currentShipment->getUdropshipVendor())
            );

            $shippingMethod  = $order->getShippingDescription();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        $page->drawText(Mage::helper('sales')->__('SOLD TO:'), 35, ($top-15) , 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(Mage::helper('sales')->__('SHIP TO:'), 285, ($top-15) , 'UTF-8');
        }
        else {
            $page->drawText(Mage::helper('sales')->__('Payment Method:'), 285, ($top-15) , 'UTF-8');
        }

        if (!$order->getIsVirtual()) {
            $y = ($top-25) - (max(count($billingAddress), count($shippingAddress)) * 10 + 5);
        }
        else {
            $y = ($top-25) - (count($billingAddress) * 10 + 5);
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top-25), 570, $y);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        $this->y = ($top-35);

        foreach ($billingAddress as $value){
            if ($value!=='') {
                $page->drawText(strip_tags(ltrim($value)), 35, $this->y, 'UTF-8');
                $this->y -=10;
            }
        }

        if (!$order->getIsVirtual()) {
            $this->y = ($top-35);
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    $page->drawText(strip_tags(ltrim($value)), 285, $this->y, 'UTF-8');
                    $this->y -=10;
                }

            }

            $this->y = $y;
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y-25);
            $page->drawRectangle(275, $this->y, 570, $this->y-25);

            $this->y -=15;
            $this->_setFontBold($page);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('sales')->__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Shipping Method:'), 285, $this->y , 'UTF-8');

            $this->y -=10;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments   = $this->y - 15;
        }
        else {
            $yPayments   = ($top-25);
            $paymentLeft = 285;
        }

        foreach ($payment as $value){
            if (trim($value)!=='') {
                $page->drawText(strip_tags(trim($value)), $paymentLeft, $yPayments, 'UTF-8');
                $yPayments -=10;
            }
        }

        if (!$order->getIsVirtual()) {
            $this->y -=15;

            $page->drawText($shippingMethod, 285, $this->y, 'UTF-8');

            $yShipments = $this->y;

            $curVendor = Mage::helper('udropship')->getVendor($this->_currentShipment->getUdropshipVendor());
            if (!$curVendor->getHidePackingslipAmount()) {
                $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " " . $order->getBaseCurrency()->formatTxt($order->getBaseShippingAmount()) . ")";

                $page->drawText($totalShippingChargesText, 285, $yShipments-7, 'UTF-8');
            }
            $yShipments -=10;
            $tracks = $order->getTracksCollection();
            if (count($tracks)) {
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(380, $yShipments, 380, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(Mage::helper('sales')->__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Number'), 385, $yShipments - 7, 'UTF-8');

                $yShipments -=17;
                $this->_setFontRegular($page, 6);
                foreach ($order->getTracksCollection() as $track) {

                    $CarrierCode = $track->getCarrierCode();
                    if ($CarrierCode!='custom')
                    {
                        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
                        $carrierTitle = $carrier->getConfigData('title');
                    }
                    else
                    {
                        $carrierTitle = Mage::helper('sales')->__('Custom Value');
                    }

                    //$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
                    $truncatedTitle = substr($track->getTitle(), 0, 45) . (strlen($track->getTitle()) > 45 ? '...' : '');
                    //$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
                    $page->drawText($truncatedTitle, 300, $yShipments , 'UTF-8');
                    $page->drawText($track->getNumber(), 395, $yShipments , 'UTF-8');
                    $yShipments -=7;
                }
            } else {
                $yShipments -= 7;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $this->y + 15, 25, $currentY);
            $page->drawLine(25, $currentY, 570, $currentY);
            $page->drawLine(570, $currentY, 570, $this->y + 15);

            $this->y = $currentY;
            $this->y -= 15;
        }
    }
}