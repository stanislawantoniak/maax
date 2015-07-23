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

                    self::processStatementsOrders($statement, $vendor);
                    self::processStatementsRma();
                    self::processStatementsRefunds($statement);
                    self::processStatementsTracks($statement);

                    self::populateStatement($statement);
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
     * This populate statement with sums of ...
     */
    public static function populateStatement($statement) {
		Mage::log($statement->getData());
    }

    /**
     * This process statements orders
     * @param GH_Statements_Model_Statement $statement
     * @param Zolago_Dropship_Model_Vendor $vendor
     */
    public static function processStatementsOrders(&$statement, $vendor) {

        /* @var Zolago_Po_Model_Resource_Po_Collection $collection */
        $collection = Mage::getResourceModel('zolagopo/po_collection');
        $collection->addVendorFilter($vendor);
        $collection->addFieldToFilter('main_table.statement_id', array('null' => true));
        $collection->addFieldToFilter('main_table.udropship_status', array('in' => array(
            Zolago_Po_Model_Source::UDPO_STATUS_SHIPPED,    // Wysłano
            Zolago_Po_Model_Source::UDPO_STATUS_DELIVERED,  // Dostarczono
            Zolago_Po_Model_Source::UDPO_STATUS_RETURNED    // Zwrocono
        )));

        foreach ($collection as $po) {
            /** @var Zolago_Po_Model_Po $po */

            // Shipping and track
            $currentShipping = $po->getLastNotCanceledShipment();
            /** @var Mage_Sales_Model_Order_Shipment_Track $track */
            $track = $currentShipping->getTracksCollection()->getFirstItem();
            $shippingCost = $currentShipping->getShippingAmountIncl();

            // Data to save
            $data = array();
            $data['statement_id'] = $statement->getId();
            $data['po_id'] = $po->getId();
            $data['po_increment_id'] = $po->getIncrementId(); // Nr zamówienia
            $data['payment_channel_owner'] = $po->getPaymentChannelOwner(); // System płatności (galeria | partner)
            $data['shipping_cost'] = 0;
            $data['shipped_date'] = $track->getShippedDate(); // Data wysyłki
            $data['carrier'] = $track->getTitle(); // Kurier
            $data['gallery_shipping_source'] = $track->getGalleryShippingSource(); // Kontrakt kurierski
            $data['payment_method'] = ucfirst(str_replace('_', ' ', $po->ghapiPaymentMethod())); // Metoda płatności

            /** @var Zolago_Po_Model_Resource_Po_Item_Collection $itemsColl */
            $itemsColl = $po->getItemsCollection();

            foreach ($itemsColl as $item) {
                /** @var Zolago_Po_Model_Po_Item $item */
                if ($item->getParentItemId()) {
                    continue; // Skip simple from configurable
                }
                $data['po_item_id'] = $item->getId();
                $data['sku'] = $item->getFinalSku();// SKU
                $data['qty'] = $item->getQty();
                $data['price'] = $item->getPriceInclTax() * $item->getQty(); // Sprzedaż przed zniżką (zł)
                $data['discount_amount'] = $item->getDiscountAmount() * $item->getQty(); // Zniżka (zł)
                $data['commission_percent'] = $item->getCommissionPercent(); // Stawka prowizji Modago
                $data['final_price'] = $item->getFinalItemPrice() * $item->getQty(); // Sprzedaż w zł

                $data['gallery_discount_value'] = 0;
                foreach ($item->getDiscountInfo() as $relation) {
                    /** @var Zolago_SalesRule_Model_Relation $relation */
                    if ($relation->getPayer() == Zolago_SalesRule_Model_Rule_Payer::PAYER_GALLERY) {
                        $data['gallery_discount_value'] += floatval($relation->getDiscountAmount()); // Zniżka finansowana przez Modago
                    }
                }

                if ($shippingCost) { // Shipping cost for first item only
                    $data['shipping_cost'] = $shippingCost; // Transport
                    $shippingCost = 0;
                }
                // (( <Sprzedaż przed zniżką> - <zniżka> + <Zniżka finansowana przez Modago>) * <Stawka prowizji Modago> ) * <podatek>
                $data['commission_value'] =
                    (($data['price'] - $data['discount_amount'] + $data['gallery_discount_value'])
                        * $data['commission_percent']) * self::getTax(); // Prowizja Modago

                // <Sprzedaż w zł> + <Transport> - <Prowizja Modago> + <Zniżka finansowana przez Modago>
                $data['value'] = $data['final_price'] + $data['shipping_cost'] - $data['commission_value'] + $data['gallery_discount_value'];

                // Save
                $statementOrder = Mage::getModel('ghstatements/order');
                $statementOrder->setData($data);
                $statementOrder->save();
            }

            // For each PO save info about statement was processed
            $po->setData('statement_id', $statement->getId());
            $po->save();
        }
    }

	/**
	 * @param GH_Statements_Model_Statement $statement
	 * @return GH_Statements_Model_Statement
	 */
    public static function processStatementsRefunds(&$statement) {
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
	    return $statement;
    }

    /**
     * This process statements tracks
     */
    public static function processStatementsTracks(&$statement) {
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
    }

    /**
     * This process statements RMA
     */
    public static function processStatementsRma() {

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

    /**
     * This return tax for statement
     *
     * @return float
     */
    public static function getTax() {
        return 1.23; // TODO
    }
}