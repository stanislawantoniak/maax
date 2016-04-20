<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Model_OrderInvoiceTotal_Shipping extends Mage_Sales_Model_Order_Invoice_Total_Shipping
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $bsaInvoiced = 0;
        foreach ($order->getInvoiceCollection() as $pi) {
            if ($pi->getBaseShippingAmount() && !$pi->isCanceled()) {
                $bsaInvoiced += $pi->getBaseShippingAmount();
            }
        }
        $bsaLeft = max(0, $order->getBaseShippingAmount() - $bsaInvoiced);
        if ($invoice->hasBaseShippingAmount()) {
            $bsaToInvoice = min($invoice->getBaseShippingAmount(), $bsaLeft);
        } else {
            $bsaToInvoice = $bsaLeft;
        }
        $_orderRate = $order->getBaseToOrderRate() > 0 ? $order->getBaseToOrderRate() : 1;
        $_incTaxRate = $order->getBaseShippingAmount() == 0 ? 1
            : $order->getBaseShippingInclTax()/$order->getBaseShippingAmount();
            
        $saToInvoice = $_orderRate*$bsaToInvoice;
        $invoice->setShippingAmount($saToInvoice);
        $invoice->setBaseShippingAmount($bsaToInvoice);
        $invoice->setShippingInclTax($order->getStore()->roundPrice($_incTaxRate*$saToInvoice));
        $invoice->setBaseShippingInclTax($order->getStore()->roundPrice($_incTaxRate*$bsaToInvoice));
        $invoice->setGrandTotal($invoice->getGrandTotal()+$saToInvoice);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$bsaToInvoice);
        return $this;
    }
}