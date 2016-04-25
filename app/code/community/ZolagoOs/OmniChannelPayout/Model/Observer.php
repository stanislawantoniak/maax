<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPayout_Model_Observer
{
    public function processStandard()
    {
        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);
        ob_implicit_flush();

        $payouts = Mage::getModel('udpayout/payout')->getCollection()
            ->setFlag('skip_offline', true)
            ->loadScheduledPayouts()
            ->addPendingPos(true)
            ->finishPayout()
            ->saveOrdersPayouts(true);

        try {
            $payouts->pay();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        
            
        Mage::helper('udpayout')->generateSchedules()->cleanupSchedules();
    }
    
    public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $id = $observer->getEvent()->getId();
        $v = Mage::helper('udropship')->getVendor($id);

        if (Mage::helper('udpayout')->isVendorEnabled($v)) {
            $block->addTab('payouts_section', array(
                'label'     => Mage::helper('udpayout')->__('Payouts'),
                'title'     => Mage::helper('udpayout')->__('Payouts'),
                'content'   => $block->getLayout()->createBlock('udpayout/adminhtml_vendor_payout_grid', 'udropship.payout.grid')
                    ->setVendorId($id)
                    ->toHtml(),
            ));
        }
    }

    public function adminhtml_version($observer)
    {
        Mage::helper('udropship')->addAdminhtmlVersion('ZolagoOs_OmniChannelPayout');
    }

    public function udropship_shipment_status_save_after($observer)
    {
        $this->_sales_order_shipment_save_after($observer);
    }
    public function sales_order_shipment_save_after($observer)
    {
        $this->_sales_order_shipment_save_after($observer);
    }
    protected function _sales_order_shipment_save_after($observer)
    {
        $po = $observer->getEvent()->getShipment();
        Mage::helper('udpayout/protected')->sales_order_shipment_save_after($po);
    }

    public function udpo_po_status_save_after($observer)
    {
        $this->_udpo_po_save_after($observer);
    }
    public function udpo_po_save_after($observer)
    {
        $this->_udpo_po_save_after($observer);
    }
    protected function _udpo_po_save_after($observer)
    {
        $po = $observer->getEvent()->getPo();
        Mage::helper('udpayout/protected')->udpo_po_save_after($po);
    }
    
    public function udropship_vendor_statement_save_before($observer)
    {
        $statement = $observer->getEvent()->getStatement();
        $statementId = $statement->getStatementId();
        if (!$statement->getId() && isset($this->_statementPayouts[$statementId])) {
            foreach ($this->_statementPayouts[$statementId] as $pt) {
                if ($pt->getPayoutStatus() != ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_CANCELED
                    && $pt->getPayoutStatus() != ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAID
                    && $pt->getPayoutStatus() != ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAYPAL_IPN
                ) {
                    $pt->setPayoutStatus(ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_HOLD);
                }
                $pt->setStatementId($statementId)->save();
            }
            $statement->getResource()->markPosHold($statement);
        }
    }
    
    protected $_statementPayouts;
    protected $_statementPayoutsByPo = array();
    public function udropship_vendor_statement_pos($observer)
    {
        $statement   = $observer->getEvent()->getStatement();
        $pos   = $observer->getEvent()->getPos();
        $statementId = $statement->getStatementId();
        $this->_statementPayouts[$statementId] = Mage::getResourceModel('udpayout/payout_collection')->loadStatementPayouts($statement, $pos);
        foreach ($this->_statementPayouts[$statementId] as $sp) {
            foreach ($sp->initTotals()->getOrders() as $sId=>$order) {
                $this->_statementPayoutsByPo[$statementId][$sId] = $sp;
            }
        }
    }
    public function udropship_vendor_statement_row($observer)
    {
        $statementId = $observer->getEvent()->getStatement()->getStatementId();
        $sId = $observer->getEvent()->getPo()->getId();
        $eData = $observer->getEvent()->getData();
        $order = &$eData['order'];
        if (isset($this->_statementPayoutsByPo[$statementId][$sId])) {
            $order['paid'] = $this->_statementPayoutsByPo[$statementId][$sId]->getPayoutStatus()==ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAID
                || $this->_statementPayoutsByPo[$statementId][$sId]->getPayoutStatus()==ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAYPAL_IPN;
        }
    }
    public function udropship_vendor_statement_item_row($observer)
    {
        $statementId = $observer->getEvent()->getStatement()->getStatementId();
        $sId = $observer->getEvent()->getPo()->getId();
        $eData = $observer->getEvent()->getData();
        $order = &$eData['order'];
        if (isset($this->_statementPayoutsByPo[$statementId][$sId])) {
            $order['paid'] = $this->_statementPayoutsByPo[$statementId][$sId]->getPayoutStatus()==ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAID
                || $this->_statementPayoutsByPo[$statementId][$sId]->getPayoutStatus()==ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAYPAL_IPN;
        }
    }
    public function udropship_vendor_statement_collect_payouts($observer)
    {
        $statement   = $observer->getEvent()->getStatement();
        $statementId = $statement->getStatementId();
        $totalPaid = 0;
        foreach ($this->_statementPayouts[$statementId] as $sp) {
            $statement->addPayout($sp);
            if ($sp->getPayoutStatus() == ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAID
                || $sp->getPayoutStatus() == ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAYPAL_IPN
            ) {
                $totalPaid += $sp->getTotalPaid();
                foreach ($sp->getAdjustments(Mage::helper('udropship')->getAdjustmentPrefix('payout')) as $adj) {
                    $statement->addAdjustment($adj);
                }
            }
        }
        $statement->setTotalPaid($statement->getTotalPaid()+$totalPaid);
    }
}
