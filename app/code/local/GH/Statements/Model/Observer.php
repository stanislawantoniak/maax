<?php

class GH_Statements_Model_Observer
{

    /**
     * todo description here
     */
    public static function processStatements() {

        /* @var $transaction Varien_Db_Adapter_Interface */
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');

        try {
            $transaction->beginTransaction();

            /* Format our dates */
            /** @var Mage_Core_Model_Date $dateModel */
            $dateModel = Mage::getModel('core/date');
            $today     = $dateModel->date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today)));

            // Collection of active vendors who have statement calendar
            /* @var $collection Unirgy_Dropship_Model_Mysql4_Vendor_Collection */
            $VendorsSollection = Mage::getResourceModel('udropship/vendor_collection');
            $VendorsSollection->addStatusFilter(Unirgy_Dropship_Model_Source::VENDOR_STATUS_ACTIVE);
            $VendorsSollection->addFieldToFilter('statements_calendar', array('neq' => null));

            foreach($VendorsSollection as $vendor) {
                /** @var Zolago_Dropship_Model_Vendor $vendor */
                $calendarId = (int)$vendor->getStatementsCalendar();

                /* @var GH_Statements_Model_Resource_Calendar_Item_Collection $itemCollection */
                $itemCollection = Mage::getResourceModel('ghstatements/calendar_item_collection');
                $itemCollection->addFieldToFilter('calendar_id', $calendarId);
                $itemCollection->addFieldToFilter('event_date', array('eq' => $yesterday));

                if ($itemCollection->getFirstItem()->getId()) {
                    /** @var GH_Statements_Model_Calendar_Item $calendarItem */
                    $calendarItem = $itemCollection->getFirstItem();

                    $statement = self::initStatement($vendor, $calendarItem);

                    $statementTotals = new stdClass();
                    $statementTotals->order = self::processStatementsOrders($statement, $vendor);
                    $statementTotals->rma = self::processStatementsRma();
                    $statementTotals->refund = self::processStatementsRefunds($statement);
                    $statementTotals->track = self::processStatementsTracks($statement);

                    self::populateStatement($statement, $statementTotals);
                }
            }

            $transaction->commit();
        } catch (Mage_Core_Exception $ex){
            // For example when 'Statement already exist'
            $transaction->rollBack();
        } catch (Exception $ex) {
            $transaction->rollBack();
            Mage::logException($ex);
        }
    }

    /**
     * This create row for statement
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param GH_Statements_Model_Calendar_Item $calendarItem
     * @throws Exception
     * @throws Mage_Core_Exception
     * @return GH_Statements_Model_Statement
     */
    public static function initStatement($vendor, $calendarItem) {

//        if (self::isStatementAlready($vendor, $calendarItem)) {
//            throw new Mage_Core_Exception(Mage::helper('ghstatements')->__('Statement already exist'));
//        }

        /** @var GH_Statements_Model_Calendar $calendar */
        $calendar = Mage::getModel('ghstatements/calendar')->load($calendarItem->getCalendarId());

        /** @var GH_Statements_Model_Statement $statement */
        $statement = Mage::getModel('ghstatements/statement');
        $statement->setData(array(
            "vendor_id"         => (int)$vendor->getId(),
            "calendar_id"       => (int)$calendarItem->getCalendarId(),
            "event_date"        => $calendarItem->getEventDate(),
            "name"              => $vendor->getVendorName() . ' ' . date("Y-m-d", strtotime($calendarItem->getEventDate())) . ' (' . $calendar->getName()  . ')'
        ));
        $statement->save();

        return $statement;
    }

    /**
     *
     * This populate statement with sums of totals
     *
     * @param GH_Statements_Model_Statement $statement
     * @param $statementTotals
     */
    public static function populateStatement(GH_Statements_Model_Statement $statement, $statementTotals)
    {
        $data = array(
            //order
            "order_commission_value" => $statementTotals->order->commissionAmount,
            "order_value" => $statementTotals->order->amount,

            //rma
            "rma_commission_value" => $statementTotals->rma->commissionAmount,
            "rma_value" => $statementTotals->rma->amount,

            //refund
            "refund_value" => $statementTotals->refund->amount,

            //track
            "tracking_charge_subtotal" => $statementTotals->track->netto,
            "tracking_charge_total" => $statementTotals->track->brutto,
        );

        $statement->addData($data);
        $statement->save();
    }

    /**
     * This process statements orders
     * @param GH_Statements_Model_Statement $statement
     * @param Zolago_Dropship_Model_Vendor $vendor
     */
    public static function processStatementsOrders(&$statement, $vendor) {
        $orderStatementTotals = new stdClass();
        $statementId = (int)$statement->getId();

        /* @var Zolago_Po_Model_Resource_Po_Collection $collection */
        $collection = Mage::getResourceModel('zolagopo/po_collection');
        $collection->addVendorFilter($vendor);
        $collection->addFieldToFilter('main_table.statement_id', array('null' => true));
        $collection->addFieldToFilter('main_table.udropship_status', array('in' => array(
            Zolago_Po_Model_Source::UDPO_STATUS_SHIPPED,    // Wysłano
            Zolago_Po_Model_Source::UDPO_STATUS_DELIVERED,  // Dostarczono
            Zolago_Po_Model_Source::UDPO_STATUS_RETURNED    // Zwrocono
        )));

        $commissionAmount = 0;
        $amount = 0;

        foreach ($collection as $po) {
            /** @var Zolago_Po_Model_Po $po */

            // Shipping and track
            $currentShipping = $po->getLastNotCanceledShipment();
            /** @var Mage_Sales_Model_Order_Shipment_Track $track */
            $track = $currentShipping->getTracksCollection()->getFirstItem();
            $shippingCost = $currentShipping->getShippingAmountIncl();

            // Data to save
            $data = array();
            $data['statement_id'] = $statementId;
            $data['po_id'] = $po->getId();
            $data['po_increment_id'] = $po->getIncrementId();
            $data['payment_channel_owner'] = $po->getPaymentChannelOwner();
            $data['shipping_cost'] = 0;
            $data['shipped_date'] = $track->getShippedDate();
            $data['carrier'] = $track->getTitle();
            $data['gallery_shipping_source'] = $track->getGalleryShippingSource();
            $data['payment_method'] = ucfirst(str_replace('_', ' ', $po->ghapiPaymentMethod()));

            $data['gallery_discount_value'] = 999; //TODO
            $data['commission_value'] = 999; //TODO


            /** @var Zolago_Po_Model_Resource_Po_Item_Collection $itemsColl */
            $itemsColl = $po->getItemsCollection();

            foreach ($itemsColl as $item) {
                /** @var Zolago_Po_Model_Po_Item $item */
                if ($item->getParentItemId()) {
                    continue; // Skip simple from configurable
                }
                $data['po_item_id'] = $item->getId();
                $data['sku'] = $item->getFinalSku();
                $data['qty'] = $item->getQty();
                $data['price'] = $item->getPriceInclTax() * $item->getQty();
                $data['discount_amount'] = $item->getDiscountAmount() * $item->getQty();
                $data['commission_percent'] = $item->getCommissionPercent();
                $data['final_price'] = $item->getFinalItemPrice() * $item->getQty();
                $data['value'] = 999; //TODO

                $commissionAmount += $data['commission_value'];
                $amount += $data['value'];

                if ($shippingCost) { // Shipping cost for first item only
                    $data['shipping_cost'] = $shippingCost;
                    $shippingCost = 0;
                }

                // Save
                $statementOrder = Mage::getModel('ghstatements/order');
                $statementOrder->setData($data);
                $statementOrder->save();
            }
        }
        $orderStatementTotals->commissionAmount = $commissionAmount;
        $orderStatementTotals->amount = $amount;

        return $orderStatementTotals;
    }

	/**
	 * @param GH_Statements_Model_Statement $statement
	 * @return GH_Statements_Model_Statement
	 */
    public static function processStatementsRefunds(&$statement) {
        $refundStatementTotals = new stdClass();

	    /** @var GH_Statements_Model_Refund $refundsStatements */
	    $refundsStatements = Mage::getModel('ghstatements/refund');

	    $dateModel = Mage::getModel('core/date');
	    $today     = $dateModel->date('Y-m-d');
	    $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today)));

	    $collection = $refundsStatements->getCollection();
	    $collection
		    ->addFieldToFilter('statement_id',array('null' => true))
		    ->addFieldToFilter('date',array('lteq' => $yesterday))
		    ->addFieldToFilter('vendor_id',$statement->getVendorId());

	    $refundValue = 0;
	    $refundIdsToUpdate = array();
	    if($collection->getSize()) {
		    foreach($collection as $refundStatement) {
			    /** @var GH_Statements_Model_Refund $refundStatement */
			    $refundValue += $refundStatement->getValue();
			    $refundIdsToUpdate[] = $refundStatement->getId();
		    }
	    }

	    if(count($refundIdsToUpdate)) {
		    /** @var GH_Statements_Model_Resource_Refund $refundStatementsResource */
		    $refundStatementsResource = $refundsStatements->getResource();
		    $refundStatementsResource->assignToStatement($statement->getId(), $refundIdsToUpdate);
	    }

	    $statement->setRefundValue($refundValue);
        $refundStatementTotals->amount = $refundValue;

	    return $refundStatementTotals;
    }

    /**
     * This process statements tracks
     */
    public static function processStatementsTracks(&$statement) {
        $trackStatementTotals = new stdClass();

        $nettoTotal = 0;
        $bruttoTotal =0;

	    $dateModel = Mage::getModel('core/date');
	    $today     = $dateModel->date('Y-m-d');
	    $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today)));

	    /** @var Mage_Sales_Model_Order_Shipment_Track $orderTracks */
	    $orderTracks = Mage::getModel('sales/order_shipment_track');

/*	    $orderTracksCollection = $orderTracks->getCollection();
	    $orderTracksCollection
		    ->addFieldToFilter('statement_id',array('null'=>true))
		    ->addFieldToFilter('gallery_shipping_source',1)
		    ->addFieldToFilter('shipped_date',array('lteq'=>$yesterday))*/


        $trackStatementTotals->netto = $nettoTotal;
        $trackStatementTotals->brutto = $bruttoTotal;

        return $trackStatementTotals;
    }

    /**
     * This process statements RMA
     */
    public static function processStatementsRma() {
        $rmaStatementTotals = new stdClass();

        $commissionAmount = 0;
        $amount = 0;

        $rmaStatementTotals->commissionAmount = $commissionAmount;
        $rmaStatementTotals->amount = $amount;

        return $rmaStatementTotals;
    }

    /**
     * This check if statement for vendor and event date is in table
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param GH_Statements_Model_Calendar_Item $calendarItem
     * @return bool
     */
    public static function isStatementAlready($vendor, $calendarItem) {

        /* @var $collection GH_Statements_Model_Resource_Statement_Collection */
        $collection = Mage::getResourceModel('ghstatements/statement_collection');
        $collection->addFieldToFilter('vendor_id', $vendor->getId());
        $collection->addFieldToFilter('event_date', date("Y-m-d", strtotime($calendarItem->getEventDate())));

        return $collection->getFirstItem()->getId() ? true : false;
    }
}