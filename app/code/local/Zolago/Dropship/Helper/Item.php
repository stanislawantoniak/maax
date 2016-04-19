<?php
class Zolago_Dropship_Helper_Item extends ZolagoOs_OmniChannel_Helper_Item {
	public function initPoTotals($po, $extendedTotals = false)
    {
        $hlp = Mage::helper('udropship');
        $isTierCom = $hlp->isModuleActive('ZolagoOs_OmniChannelTierCommission');
        $vendor = $hlp->getVendor($po->getUdropshipVendor());
        $order = $po->getOrder();
        $statement = Mage::getModel('udropship/vendor_statement')->setVendor($vendor)->setVendorId($vendor->getId());
        $totals = $statement->getEmptyTotals(true);
        $totals_amount = $statement->getEmptyTotals();
        $hlp->collectPoAdjustments(array($po), true);
        $stOrders = array();
		
		$helper			= Mage::helper('zolagodropship');
		$subtotalExcl	= 0;
		$subtotalIncl	= 0;
		$tax			= 0;
		
        if ($isTierCom) {
            $onlySubtotal = false;
            foreach ($po->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) continue;
                $stOrder = $statement->initPoItem($item, $onlySubtotal);
                $onlySubtotal = true;
                $stOrder = $statement->calculateOrder($stOrder);
                $totals_amount = $statement->accumulateOrder($stOrder, $totals_amount);
                $stOrders[$item->getId()] = $stOrder;
            }
        } else {
            $stOrder = $statement->initOrder($po);
            $stOrder = $statement->calculateOrder($stOrder);
            $totals_amount = $statement->accumulateOrder($stOrder, $totals_amount);
        }
        $this->formatOrderAmounts($order, $totals, $totals_amount, 'merge');
        $poTotals = array();
        foreach ($totals as $tKey=>$tValue) {
            $tLabel = false;
            switch ($tKey) {
                case 'subtotal':
                    $tLabel = $hlp->__('Subtotal');
                    break;
                case 'com_percent':
                    if (!$isTierCom) {
                        $tLabel = $hlp->__('Commission Percent');
                    }
                    break;
                case 'trans_fee':
                    $tLabel = $hlp->__('Transaction Fee');
                    break;
                case 'com_amount':
                    $tLabel = $hlp->__('Commission Amount');
                    break;
                case 'adj_amount':
                    if ($tValue>0) {
                        $tLabel = $hlp->__('Adjustment');
                    }
                    break;
                case 'total_payout':
                    $tLabel = $hlp->__('Total Payout');
                    break;
                case 'tax':
                    if (in_array($vendor->getStatementTaxInPayout(), array('', 'include'))) {
                        $tLabel = $hlp->__('Tax Amount');
                    }
                    break;
                case 'discount':
                    if (in_array($vendor->getStatementDiscountInPayout(), array('', 'include'))) {
                        $tLabel = $hlp->__('Discount');
                    }
                    break;
                case 'shipping':
                    if (in_array($vendor->getStatementShippingInPayout(), array('', 'include'))) {
                        $tLabel = $hlp->__('Shipping');
                    }
                    break;
            }
            if ($tLabel) {
                $poTotals[] = array(
                    'label' => $tLabel,
                    'value' => $tValue
                );
            }
        }

        foreach ($po->getAllItems() as $poItem) {
            if ($poItem->getOrderItem()->getParentItem()) continue;
            $item = $poItem->getOrderItem();
            $itemAmounts = $addOptions = array();

			if ($extendedTotals) {
				$itemAmounts['price_incl_tax'] = $item->getPriceInclTax();
				$addOptions[] = array(
					'label' => $hlp->__('Price Incl. Tax'),
					'value' => $this->formatBasePrice($order, $item->getPriceInclTax())
				);				
			}
			
            $itemAmounts['cost'] = $item->getBaseCost();
            $itemAmounts['row_cost'] = $item->getBaseCost()*$poItem->getQty();
            $itemAmounts['price'] = $item->getBasePrice();
            $itemAmounts['row_total'] = $item->getBasePrice()*$poItem->getQty();
            if ($vendor->getStatementSubtotalBase() == 'cost') {
                $addOptions[] = array(
                    'label' => $hlp->__('Cost'),
                    'value' => $this->formatBasePrice($order, $item->getBaseCost())
                );
                if ($poItem->getQty()>1) {
                    $addOptions[] = array(
                        'label' => $hlp->__('Row Cost'),
                        'value' => $this->formatBasePrice($order, $item->getBaseCost()*$poItem->getQty())
                    );
                }
            } else {
                $addOptions[] = array(
                    'label' => $hlp->__('Price'),
                    'value' => $this->formatBasePrice($order, $item->getBasePrice())
                );
                if ($poItem->getQty()>1) {
                    $addOptions[] = array(
                        'label' => $hlp->__('Row Total'),
                        'value' => $this->formatBasePrice($order, $item->getBasePrice()*$poItem->getQty())
                    );
                }
            }
            $iTax = $item->getBaseTaxAmount()/max(1,$item->getQtyOrdered());
            $iTax = $iTax*$poItem->getQty();
            $itemAmounts['tax'] = $iTax;
            if ($item->getBaseTaxAmount() && in_array($vendor->getStatementTaxInPayout(), array('', 'include'))) {
                $addOptions[] = array(
                    'label' => $hlp->__('Tax Amount'),
                    'value' => $this->formatBasePrice($order, $iTax)
                );
            }
            $iDiscount = $item->getBaseDiscountAmount()/max(1,$item->getQtyOrdered());
            $iDiscount = $iDiscount*$poItem->getQty();
            $itemAmounts['discount'] = $iDiscount;
            if ($item->getBaseDiscountAmount() && in_array($vendor->getStatementDiscountInPayout(), array('', 'include'))) {
                $addOptions[] = array(
                    'label' => $hlp->__('Discount'),
                    'value' => $this->formatBasePrice($order, $iDiscount)
                );
            }
            if ($isTierCom) {
                $itemAmounts['com_percent'] = $stOrders[$poItem->getId()]['com_percent'];
                $itemAmounts['com_amount'] = $stOrders[$poItem->getId()]['amounts']['com_amount'];
                if ($isTierCom && isset($stOrders[$poItem->getId()]['com_percent']) && $stOrders[$poItem->getId()]['com_percent']>0) {
                    $addOptions[] = array(
                        'label' => $hlp->__('Commission Percent'),
                        'value' => sprintf('%s%%', $stOrders[$poItem->getId()]['com_percent'])
                    );
                    if (isset($stOrders[$poItem->getId()]['amounts']['com_amount'])) {
                    $addOptions[] = array(
                        'label' => $hlp->__('Commission Amount'),
                        'value' => $this->formatBasePrice($order, $stOrders[$poItem->getId()]['amounts']['com_amount'])
                    );
                    }
                }
            }
			
			$subtotalExcl	+= $item->getPrice() * $item->getQtyOrdered();
			$subtotalIncl	+= $item->getPriceInclTax() * $item->getQtyOrdered();
			$tax			+= $item->getTaxAmount();
			
            $poItem->setUdropshipTotalAmounts($itemAmounts);
            $poItem->setUdropshipTotals($addOptions);
        }		
		
		if ($extendedTotals) {
			$poTotals['subtotal_excl'] = array(
				'label' => $helper->__('Subtotal Excl. Tax'),
				'value' => $this->formatBasePrice($order, $subtotalExcl)
			);

			$poTotals['tax'] = array(
				'label' => $helper->__('Tax Value'),
				'value' => $this->formatBasePrice($order, $tax)
			);		

			$shippingInclTax = $po->getShippingAmountIncl();
			
			$poTotals['shipping'] = array(
				'label' => $helper->__('Shipping Cost'),
				'value' => $this->formatBasePrice($order, $shippingInclTax)
			);
			
			$total = $subtotalIncl + $shippingInclTax;
			$poTotals['total'] = array(
				'label' => $helper->__('Totals'),
				'value' => $this->formatBasePrice($order, $total)
			);
		}
		
        $po->setUdropshipTotalAmounts($totals_amount);
        $po->setUdropshipTotals($poTotals);		
    }
}