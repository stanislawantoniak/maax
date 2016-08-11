<?php

/**
 * Mamy 3 strumienie pieniężne:
 * 1.   Płatności za zamówienia
 * 1.1      (-) Wysyłka zamówienia (brutto)
 * 1.2      (+) Zwrot gotówki w RMA (cała wartość)
 * 2.   Prowizje Modago
 * 2.1      Wysyłka zamówienia:
 *          (+) Prowizja [*]
 *          (-) Rabat finansowany przez Modago [#]
 * 2.2      Terminalny (pozytywny) status RMA (zwrot liczony procentowo*)
 *          (-) Prowizja [*]
 *          (+) Rabat finansowany przez Modago [#]
 * 3.   Koszty transportu, marketingu
 *
 * NOTE *:
 * Prowizna od zwrotów powinna być liczona proporcjonalnie do kwoty zwrotu,
 * tylko dla nieodebranych przesyłek COD powinna być wyliczana od całości zamówienia
 *
 * Class GH_Statements_Model_Observer
 */
class GH_Statements_Model_Observer
{

    /**
     * This function is fire by cron
     * Process all statements for gallery and vendors for:
     * Orders, RMA, refunds and tracks
     * @param string|null $forceCustomDate
     */
    public static function processStatements($object,$forceCustomDate = null) {

        /* @var $transaction Varien_Db_Adapter_Interface */
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        $alreadyExists = array();

        /* Format our dates */
        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getModel('core/date');
        $today     = $dateModel->date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today)));
        if ($forceCustomDate) {
            $yesterday = $forceCustomDate;
        }

        // Collection of active vendors who have statement calendar
        /* @var $collection ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection */
        $vendorsCollection = Mage::getResourceModel('udropship/vendor_collection');
        $vendorsCollection->addStatusFilter(ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_ACTIVE);
        $vendorsCollection->addFieldToFilter('statements_calendar', array('neq' => null));

        foreach($vendorsCollection as $vendor) {
            /** @var Zolago_Dropship_Model_Vendor $vendor */
            $calendarId = (int)$vendor->getStatementsCalendar();
            
            /* @var GH_Statements_Model_Resource_Calendar_Item_Collection $itemCollection */
            $itemCollection = Mage::getResourceModel('ghstatements/calendar_item_collection');
            $itemCollection->addFieldToFilter('calendar_id', $calendarId);
            $itemCollection->addFieldToFilter('event_date', array('eq' => $yesterday));
            if ($itemCollection->getSize() && $itemCollection->getFirstItem()->getId()) {
                /** @var GH_Statements_Model_Calendar_Item $calendarItem */
                $calendarItem = $itemCollection->getFirstItem();

                try {
                    $transaction->beginTransaction();

                    $statement = self::initStatement($vendor, $calendarItem, $forceCustomDate);

                    $statementTotals = new stdClass();
                    $statementTotals->order = self::processStatementsOrders($statement, $vendor);
                    $statementTotals->rma = self::processStatementsRma($statement);
                    $statementTotals->refund = self::processStatementsRefunds($statement);
                    $statementTotals->track = self::processStatementsTracks($statement);
                    $statementTotals->marketing = self::processStatementsMarketing($statement);
                    $statementTotals->payment = self::processStatementsPayment($statement);
                    $statementTotals->correction = self::processStatementsInvoice($statement);
                    $statementTotals->lastBalance = self::processStatementLastBalance($statement);

                    self::populateStatement($statement, $statementTotals);


                } catch(Mage_Core_Exception $e) {
                    Mage::log($e->getMessage(), null, 'ghstatements_cron_exception.log');
                    $alreadyExists[] = $e->getMessage();
                    $transaction->rollBack();
                } catch (Exception $ex) {
                    $transaction->rollBack();
                    Mage::logException($ex);
                }

                $transaction->commit();
            }
        }
    }


    /**
     * Calculate vendor balance based on
     *
     * 1. Customer payments
     * 2. Customer refunds
     * 3. Payouts to vendor
     * 4. Invoices and credit notes
     *
     * 5. Balance
     * 5.1. Monthly balance
     * 5.2. Cumulative balance
     * 5.3. Due balance
     *
     *
     */
    public static function calculateVendorBalance()
    {
        $resourceBalance = Mage::getResourceModel("ghstatements/vendor_balance");
        $resourceBalance->calculateVendorBalanceData();
    }

    /**
     * calculating date from and last balance
     */

    public static function processStatementLastBalance($statement) {
        $balance = 0;
        $dateFrom = 0;
        $collection = Mage::getModel('ghstatements/statement')->getCollection();
        $collection->addFieldToFilter('vendor_id',$statement->getVendorId());
        $collection->addFieldToFilter('id',array('neq'=>$statement->getId()));
        $collection->getSelect()->order('id DESC');
        if ($item = $collection->getFirstItem()) {
            if ($item->getId()) {
                $balance = $item->getData('to_pay') - $item->getData('payment_value')+$item->getData('last_statement_balance');            
                $dateFrom = strtotime($item->getData('event_date'))+24*3600; // next day
            }
        }
        $lastBalance = new StdClass();
        $lastBalance->balance = $balance;
        $lastBalance->dateFrom = $dateFrom;
        return $lastBalance;
    }
    /**
     * This create row for statement
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param GH_Statements_Model_Calendar_Item $calendarItem
     * @param string|null $forceCustomDate
     * @throws Exception
     * @throws Mage_Core_Exception
     * @return GH_Statements_Model_Statement
     */
    public static function initStatement($vendor, $calendarItem, $forceCustomDate = null) {

        if (self::isStatementAlready($vendor, $calendarItem)) {
            throw new Mage_Core_Exception(Mage::helper('ghstatements')->__('Statement for date %s and vendor %s already exist',$calendarItem->getEventDate(),$vendor->getVendorName()));
        }

        /** @var GH_Statements_Model_Calendar $calendar */
        $calendar = Mage::getModel('ghstatements/calendar')->load($calendarItem->getCalendarId());

        /** @var GH_Statements_Model_Statement $statement */
        $statement = Mage::getModel('ghstatements/statement');
        $statement->setData(array(
                                "vendor"            => $vendor,
                                "vendor_id"         => (int)$vendor->getId(),
                                "calendar_id"       => (int)$calendarItem->getCalendarId(),
                                "event_date"        => $calendarItem->getEventDate(),
                                "name"              => $vendor->getVendorName() . ' ' . date("Y-m-d", strtotime($calendarItem->getEventDate())) . ' (' . $calendar->getName()  . ')'
                            ));
        $statement->save();
        if ($forceCustomDate) {
            $statement->setForceCustomDate($forceCustomDate);
        }

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
            'to_pay'                 => 0, // Do wypłaty dla Vendora
            'total_commission'       => 0, // Suma prowizji
            'gallery_discount_value' => 0, // Suma zniżek finansowanych przez Modago
            'actual_balance'         => 0  // Bieżące saldo rozliczenia
        );
        // Order
        if (!empty($statementTotals->order)) {
            $data["order_commission_value"]       = $statementTotals->order->commissionAmount;     // Suma prowizji z zamówień
            $data["total_commission"]            += $statementTotals->order->commissionAmount;     // Suma prowizji
            $data["to_pay"]                      += $statementTotals->order->galleryPayment;       // Do wypłaty dla Vendora
            $data["gallery_discount_value"]       = $statementTotals->order->galleryDiscountValue; // Suma zniżek finansowanych przez Modago
            $data["order_gallery_discount_value"] = $statementTotals->order->galleryDiscountValue;
            $data['order_value']                  = $statementTotals->order->galleryPayment;       // Suma zamówień w kanale płatności Modago
        }
        // Rma
        if (!empty($statementTotals->rma)) {
            $data["rma_commission_value"]       = $statementTotals->rma->commissionAmount;          // Suma prowizji z RMA ( równoważne order_commission_value )
            $data["rma_value"]                  = $statementTotals->rma->galleryCommissionValue;    // Suma zwrotów z RMA
            $data['total_commission']          -= $statementTotals->rma->galleryCommissionValue;    // Suma prowizji
            $data["gallery_discount_value"]    -= $statementTotals->rma->galleryDiscountValue;      // Suma zniżek finansowanych przez Modago
            $data["rma_gallery_discount_value"] = $statementTotals->rma->galleryDiscountValue;
            $data["to_pay"]                    -= $data["total_commission"];
            $data["to_pay"]                    += $data["gallery_discount_value"];
        }
        // Refund
        if (!empty($statementTotals->refund)) {
            $data["refund_value"]               = $statementTotals->refund->amount; // Suma kwot do zwrotu
            $data['to_pay']                    -= $statementTotals->refund->amount;
        }
        // Track
        if (!empty($statementTotals->track)) {
            $data["tracking_charge_subtotal"]   = $statementTotals->track->netto;
            $data["tracking_charge_total"]      = $statementTotals->track->brutto;
            $data['to_pay']                    -= $statementTotals->track->brutto;
        }
        // Marketing
        if(!empty($statementTotals->marketing)) {
            $data["marketing_value"]            = $statementTotals->marketing->amount;
            $data['to_pay']                    -= $statementTotals->marketing->amount;
        }
        // correction
        if (!empty($statementTotals->correction)) {
            $data['commission_correction']      = $statementTotals->correction->commissionCorrection;
            $data['delivery_correction']        = $statementTotals->correction->deliveryCorrection;
            $data['marketing_correction']       = $statementTotals->correction->marketingCorrection;
            $data['to_pay']                    += $statementTotals->correction->commissionCorrection;
            $data['to_pay']                    += $statementTotals->correction->deliveryCorrection;
            $data['to_pay']                    += $statementTotals->correction->marketingCorrection;
        }
        // Payment
        if(!empty($statementTotals->payment)) {
            $data["payment_value"]              = $statementTotals->payment->amount;
            $data["actual_balance"]            -= $statementTotals->payment->amount;
        }
        if(!empty($statementTotals->lastBalance)) {
            $data["last_statement_balance"]     = $statementTotals->lastBalance->balance;
            $data["actual_balance"]            += $statementTotals->lastBalance->balance;
            $data["actual_balance"]            += $data['to_pay'];
            if ($statementTotals->lastBalance->dateFrom) {
                $data["date_from"]              = date("Y-m-d",$statementTotals->lastBalance->dateFrom);
            }
        }
        $data['total_commission_netto'] = round($data['total_commission']/self::getTax(),2,PHP_ROUND_HALF_UP);
        if (!empty($data)) {
            $statement->addData($data);
            $statement->save();
        }
    }

    /**
     * This process statements orders
     * @param GH_Statements_Model_Statement $statement
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @return stdClass
     * @throws Exception
     */
    public static function processStatementsOrders($statement, $vendor) {
        $orderStatementTotals = new stdClass();

        /* @var Zolago_Po_Model_Resource_Po_Collection $collection */
        $collection = Mage::getResourceModel('zolagopo/po_collection');
        $collection->addVendorFilter($vendor);
        $collection->addFieldToFilter('main_table.statement_id', array('null' => true));
        $collection->addFieldToFilter('main_table.udropship_status', array('in' => array(
                                          Zolago_Po_Model_Source::UDPO_STATUS_SHIPPED,    // Wysłano
                                          Zolago_Po_Model_Source::UDPO_STATUS_DELIVERED,  // Dostarczono
                                          Zolago_Po_Model_Source::UDPO_STATUS_RETURNED    // Zwrocono
                                      )));

        $commissionAmount  = 0; // suma prowizji z zamówień
        $amount            = 0; // suma wartości zamówień
        $discountValue     = 0; // suma zniżek finansowanych przez Modago
        $orderGalleryValue = 0; // wartość zamówień w kanale płatności modago

        $dateModel = Mage::getModel('core/date');
        $today     = $dateModel->date('Y-m-d');
        $yesterday = date('Y-m-d',strtotime('yesterday',strtotime($today)));
        if ($statement->getForceCustomDate()) {
            $yesterday = $statement->getForceCustomDate();
        }

        foreach ($collection as $po) {
            /** @var Zolago_Po_Model_Po $po */

            // Shipping and track
            $currentShipping = $po->getLastNotCanceledShipment();

            if($currentShipping) {
                /** @var Mage_Sales_Model_Order_Shipment_Track $track */
                $track = $currentShipping->getTracksCollection()->getFirstItem();

                // Only PO with track shipped, delivered or returned
                // and shipped date <= yesterday
                $shippedDateInt = strtotime($track->getShippedDate());
                if ($shippedDateInt && $shippedDateInt > strtotime($yesterday)) {
                    continue;
                }

                $shippingCost = $currentShipping->getShippingAmountIncl();

                // Data to save
                $data = array();
                $data['statement_id'] = $statement->getId();
                $data['po_id'] = $po->getId();
                $data['po_increment_id'] = $po->getIncrementId(); // Nr zamówienia
                $data['payment_channel_owner'] = $po->getPaymentChannelOwner(); // System płatności (galeria | partner)
                $data['charge_commission_flag'] = $po->getChargeCommissionFlag(); // Czy naliczać prowizje w rozliczeniu?
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
                    $data['discount_amount'] = $item->getDiscountAmount(); // Zniżka (zł)
                    $data['commission_percent'] = $item->getCommissionPercent(); // Stawka prowizji Modago
                    $data['final_price'] = $item->getFinalItemPrice() * $item->getQty(); // Sprzedaż w zł

                    $data['gallery_discount_value'] = 0;
                    foreach ($item->getDiscountInfo() as $relation) {
                        /** @var Zolago_SalesRule_Model_Relation $relation */
                        if ($relation->getPayer() == Zolago_SalesRule_Model_Rule_Payer::PAYER_GALLERY) {
                            $data['gallery_discount_value'] += floatval($relation->getDiscountAmount()); // Zniżka finansowana przez Modago
                        }
                    }

                    $data['shipping_cost'] = 0;
                    if ($shippingCost) { // Shipping cost for first item only
                        $data['shipping_cost'] = $shippingCost; // Transport
                        $shippingCost = 0;
                    }
					if ($po->getChargeCommissionFlag()) { // Flaga czy naliczać prowizje w rozliczeniu?
						// (( <Sprzedaż przed zniżką> - <zniżka> + <Zniżka finansowana przez Modago>) * <Stawka prowizji Modago> ) * <podatek>
						$data['commission_value'] =
							(($data['price'] - $data['discount_amount'] + $data['gallery_discount_value'])
								* (floatval($data['commission_percent']) / 100)) * self::getTax(); // Prowizja Modago
						$data['commission_value'] = round($data['commission_value'], 2, PHP_ROUND_HALF_UP);
					} else {
						$data['commission_value'] = 0;
					}
                    if ($po->getPaymentChannelOwner() == Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL) {
                        // kwota zamówień
                        $orderGalleryValue += $data['final_price']+$data['shipping_cost'];  // kwota brutto 
                        // <Sprzedaż w zł> + <Transport> - <Prowizja Modago> + <Zniżka finansowana przez Modago>
                        $data['value'] = $data['final_price'] + $data['shipping_cost'] - $data['commission_value'] + $data['gallery_discount_value']; // Do wypłaty
                    } else {
                        // - <prowizja modago> + <Zniżka finansowana przez Modago>
                        $data['value'] = $data['gallery_discount_value'] - $data['commission_value']; // Do wypłaty
                    }
                    $data['value'] = round($data['value'], 2, PHP_ROUND_HALF_UP);
                    $discountValue += $data['gallery_discount_value'];
                    $commissionAmount += $data['commission_value'];
                    $amount += $data['value']; // do wypłaty

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
        $orderStatementTotals->commissionAmount = $commissionAmount;
        $orderStatementTotals->amount = $amount;
        $orderStatementTotals->galleryDiscountValue = $discountValue;
        $orderStatementTotals->galleryPayment = $orderGalleryValue;


        return $orderStatementTotals;
    }

    /**
     * @param GH_Statements_Model_Statement $statement
     * @return GH_Statements_Model_Statement
     */
    public static function processStatementsRefunds($statement) {
        $refundStatementTotals = new stdClass();

        /** @var GH_Statements_Model_Refund $refundsStatements */
        $refundsStatements = Mage::getModel('ghstatements/refund');

        $dateModel = Mage::getModel('core/date');
        $today     = $dateModel->date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today)));

        if ($statement->getForceCustomDate()) {
            $yesterday = $statement->getForceCustomDate();
        }

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

        $refundStatementTotals->amount = $refundValue;

        return $refundStatementTotals;
    }

    /**
     * This process statements tracks
     */
    public static function processStatementsTracks($statement) {
        $trackStatementTotals = new stdClass();

        $nettoTotal = 0;
        $bruttoTotal =0;

        $dateModel = Mage::getModel('core/date');
        $today     = $dateModel->date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today)));

        if ($statement->getForceCustomDate()) {
            $yesterday = $statement->getForceCustomDate();
        }

        $trackStatements = array();
        $tax = self::getTax();

        /** @var Mage_Sales_Model_Order_Shipment $ordersShipments */
        $ordersShipments = Mage::getModel('sales/order_shipment');

        //orders tracking start

        //load orders shipments
        $orderShipmentsCollection = $ordersShipments->getCollection();
        $orderShipmentsCollection
        ->addFieldToFilter('statement_id',array('null' => true))
        ->addFieldToFilter('udropship_vendor',$statement->getVendorId())
        ->addFieldToFilter('udropship_status',array('neq'=>ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_CANCELED));

        //prepare arrays to load orders shipments trackings
        $ordersShipmentsIds = array();
        $ordersShipmentsObjects = array();
        if($orderShipmentsCollection->getSize()) {
            foreach($orderShipmentsCollection as $orderShipment) {
                /** @var Mage_Sales_Model_Order_Shipment $orderShipment */
                $ordersShipmentsIds[] = $orderShipment->getId();
                $ordersShipmentsObjects[$orderShipment->getId()] = $orderShipment;
            }
        }

        if(count($ordersShipmentsIds)) {

            //load orders shipments tracks based on shimpents ids gathered in previous loop
            /** @var Mage_Sales_Model_Order_Shipment_Track $ordersTracks */
            $ordersTracks = Mage::getModel('sales/order_shipment_track');

            $ordersTracksCollection = $ordersTracks->getCollection();
            $ordersTracksCollection
            ->addFieldToFilter('statement_id', array('null' => true))
            ->addFieldToFilter('gallery_shipping_source', 1)
            ->addFieldToFilter('shipped_date', array('notnull' => true))
            ->addFieldToFilter('shipped_date', array('lteq' => $yesterday))
            ->addFieldToFilter('parent_id',array('in'=>$ordersShipmentsIds))
            ->addFieldToFilter('udropship_status',array('in'=>array(
                                   ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_SHIPPED,
                                   ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED,
                                   Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED
                               )));

            //not all shipments selected in previous loop will be adequate to update so we have to collect shipments ids once again based on loaded trackings
            $ordersShipmentsIdsToUpdate = array();
            if($ordersTracksCollection->getSize()) {
                foreach($ordersTracksCollection as $orderTrack) {
                    /** @var Mage_Sales_Model_Order_Shipment_Track $orderTrack */
                    $shipmentId = $orderTrack->getParentId();
                    $shipment = $ordersShipmentsObjects[$shipmentId];
                    $ordersShipmentsIdsToUpdate[] = $shipmentId;

                    $chargeTotal = $orderTrack->getChargeTotal() * $tax;
                    $chargeTotal = round($chargeTotal, 2, PHP_ROUND_HALF_UP);

	                $po = Mage::getModel("udropship/po")->load($shipment->getUdpoId());

				    //prepare array to insert into gh_statements_track
				    $trackStatements[] = array(
					    'statement_id'      => $statement->getId(),
					    'po_id'             => $shipment->getUdpoId(),
					    'po_increment_id'   => $shipment->getUdpoIncrementId(),
					    'rma_id'            => null,
					    'rma_increment_id'  => null,
					    'shipped_date'      => $orderTrack->getShippedDate(),
					    'track_number'      => $orderTrack->getTrackNumber(),
					    'charge_shipment'   => $orderTrack->getChargeShipment(),
					    'charge_fuel'       => $orderTrack->getChargeFuel(),
					    'charge_insurance'  => $orderTrack->getChargeInsurance(),
					    'charge_cod'        => $orderTrack->getChargeCod(),
					    'charge_subtotal'   => $orderTrack->getChargeTotal(),
					    'charge_total'      => $chargeTotal,
					    'track_type'        => $orderTrack->getTrackType(),
					    'title'             => $orderTrack->getTitle(),
					    'customer_id'       => $po->getCustomerId(),
					    'sales_track_id'    => $orderTrack->getEntityId(),
					    'rma_track_id'		=> null,
                        'shipping_source_account' => $orderTrack->getShippingSourceAccount(),
				    );

				    $nettoTotal += $orderTrack->getChargeTotal();
				    $bruttoTotal += $chargeTotal;
			    }
		    }
		    //orders tracking end

		    //rmas tracking start
		    /** @var Zolago_Rma_Model_Rma_Track $rmasTracks */
		    $rmasTracks = Mage::getModel('urma/rma_track');

		    $rmasTracksCollection = $rmasTracks->getCollection();
		    $rmasTracksCollection
			    ->addFieldToFilter('main_table.statement_id', array('null' => true))
			    ->addFieldToFilter('main_table.gallery_shipping_source', 1)
                ->addFieldToFilter('main_table.shipped_date', array('notnull' => true))
                ->addFieldToFilter('main_table.shipped_date', array('lteq' => $yesterday))
			    ->addFieldToFilter('main_table.udropship_status',array('in'=>array(
				    ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_SHIPPED,
				    ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED,
				    Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED
			    )))
			    ->getSelect()
			        ->join(
				        'urma_rma',
				        'main_table.parent_id = urma_rma.entity_id',
				        array('udropship_vendor')
			        )
			        ->where('urma_rma.udropship_vendor = '.$statement->getVendorId());

		    $rmasTracksToUpdate = array();
			if($rmasTracksCollection->getSize()) {
				foreach($rmasTracksCollection as $rmaTrack) {
					/** @var Zolago_Rma_Model_Rma_Track $rmaTrack */

					/** @var Zolago_Rma_Model_Rma $rma */
					$rma = Mage::getModel("zolagorma/rma")->load($rmaTrack->getParentId());

					if($rma && $rma->getId()) {
						$rmasTracksToUpdate[] = $rmaTrack->getId();

						$chargeTotal = $rmaTrack->getChargeTotal() * $tax;
                        $chargeTotal = round($chargeTotal, 2, PHP_ROUND_HALF_UP);

						/** @var Zolago_Po_Model_Po $po */
						$po = $rma->getPo();

						//prepare array to insert into gh_statements_track
						$trackStatements[] = array(
							'statement_id'      => $statement->getId(),
							'po_id'             => $po->getId(),
							'po_increment_id'   => $po->getIncrementId(),
							'rma_id'            => $rma->getId(),
							'rma_increment_id'  => $rma->getIncrementId(),
							'shipped_date'      => $rmaTrack->getShippedDate(),
							'track_number'      => $rmaTrack->getTrackNumber(),
							'charge_shipment'   => $rmaTrack->getChargeShipment(),
							'charge_fuel'       => $rmaTrack->getChargeFuel(),
							'charge_insurance'  => $rmaTrack->getChargeInsurance(),
							'charge_cod'        => $rmaTrack->getChargeCod(),
							'charge_subtotal'   => $rmaTrack->getChargeTotal(),
							'charge_total'      => $chargeTotal,
							'track_type'        => $rmaTrack->getTrackType(),
							'title'             => $rmaTrack->getTitle(),
							'customer_id'       => $po->getCustomerId(),
							'sales_track_id'	=> null,
                            'rma_track_id'      => $rmaTrack->getEntityId(),
                            'shipping_source_account' => $rmaTrack->getShippingSourceAccount()
						);

						$nettoTotal += $rmaTrack->getChargeTotal();
						$bruttoTotal += $chargeTotal;
					}
				}
			}
		    //rmas tracking end

		    if(count($trackStatements)) {
			    /** @var GH_Statements_Model_Track $trackStatement */
			    $trackStatement = Mage::getModel("ghstatements/track");

			    /** @var GH_Statements_Model_Resource_Track $trackStatementResource */
			    $trackStatementResource = $trackStatement->getResource();
			    $trackStatementResource->appendTracks($trackStatements);
		    }

		    if(count($ordersShipmentsIdsToUpdate)) {
			    foreach($ordersShipmentsIdsToUpdate as $shipmentId) {
				    /** @var Mage_Sales_Model_Order_Shipment $currentShipment */
				    $currentShipment = $ordersShipmentsObjects[$shipmentId];
				    $currentShipment->setStatementId($statement->getId());
				    $currentShipment->save();
			    }
		    }


			//set tracking statement_id
			if ($ordersTracksCollection->getSize()) {
				unset($orderTrack);
				foreach ($ordersTracksCollection as $orderTrack) {
					/** @var Mage_Sales_Model_Order_Shipment_Track $orderTrack */
					$orderTrack->setStatementId($statement->getId());
					$orderTrack->setWebApi(true);
					$orderTrack->save();
				}
			}
			if($rmasTracksCollection->getSize()) {
				unset($rmaTrack);
				foreach($rmasTracksCollection as $rmaTrack) {
					/** @var Zolago_Rma_Model_Rma_Track $rmaTrack */
					$rmaTrack->setStatementId($statement->getId());
					$rmaTrack->setWebApi(true);
					$rmaTrack->save();
				}
			}
			//--set tracking statement_id

		    $trackStatementTotals->netto = $nettoTotal;
		    $trackStatementTotals->brutto = $bruttoTotal;

		    return $trackStatementTotals;
	    }
    }

    /**
     * This process statements RMA
     */
    public static function processStatementsRma($statement) {
        $rmaStatementTotals = new stdClass();
        $commissionAmount = 0;
        $amount = 0;

        $dateModel = Mage::getModel('core/date');
        $today     = $dateModel->date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today))) . ' 23:59:59';

        if ($statement->getForceCustomDate()) {
            $yesterday = $statement->getForceCustomDate() . ' 23:59:59';
        }

        // Zwroty o statusie zamknięte-zrealizowane, które mają kwotę do zwrotu lub o typie zwrot nieodebranej niezależnie od kwoty do zwrotu
        // Te które nie zostały dotąd ujęte w rozliczeniu

        /** @var ZolagoOs_Rma_Model_Mysql4_Rma_Item_Collection $rmaItemsColl  */
        $rmaItemsColl = Mage::getResourceModel('urma/rma_item_collection');
        $rmaItemsColl->addFieldToFilter('main_table.statement_id', array('null' => true));
        $rmaItemsColl->getSelect()
        ->join(
            'urma_rma',
            'main_table.parent_id = urma_rma.entity_id',
            array(
                'udropship_vendor', 'rma_status', 'rma_type', 'payment_channel_owner', 'udpo_id',
                'udpo_increment_id', 'increment_id', 'created_at')
        )
        ->where('urma_rma.udropship_vendor = ' . $statement->getVendorId());
        $rmaItemsColl->addFieldToFilter('urma_rma.rma_status',Zolago_Rma_Model_Rma_Status::STATUS_CLOSED_ACCEPTED);
        $rmaItemsColl->addFieldToFilter('urma_rma.updated_at', array('lteq' => $yesterday));

        //store already loaded rmas in arrays to prevent double loading them on different rma items
        $rmas = array();
        $pos = array();

        $galleryDiscountValue = 0;
        $galleryCommissionValue = 0;
        foreach ($rmaItemsColl as $rmaItem) {
            /** @var Zolago_Rma_Model_Rma_Item $rmaItem */
            if (!$rmaItem->getProductId()) {
                continue; // Shipping cost
            }

            /** @var Zolago_Rma_Model_Rma $rma */
            $rmaId = $rmaItem->getParentId();
            if(!isset($rmas[$rmaId])) {
                $rma = Mage::getModel('zolagorma/rma')->load($rmaId);
                $rmas[$rmaId] = $rma;
            } else {
                $rma = $rmas[$rmaId];
            }

            /** @var Zolago_Po_Model_Po_Item $poItem */
            $poItem = $rmaItem->getPoItem();

            /** @var Zolago_Po_Model_Po $po */
            $poId = $poItem->getParentId();
            if(!isset($pos[$poId])) {
                $po = Mage::getModel('zolagopo/po')->load($poId);
                $pos[$poId] = $po;
            } else {
                $po = $pos[$poId];
            }

            //undelivered cod order
            $packageReturned = $rma->getRmaType() == Zolago_Rma_Model_Rma::RMA_TYPE_RETURN && $po->isCod();

            if (!floatval($rmaItem->getReturnedValue()) && !$packageReturned) {
                continue; // No value to return
            }

            $poItem = $rmaItem->getPoItem();

            $data = array();
            $data["statement_id"]           = $statement->getId();
            $data["po_id"]                  = $rmaItem->getUdpoId();
            $data["po_increment_id"]        = $rmaItem->getUdpoIncrementId();
            $data["rma_id"]                 = $rmaItem->getParentId();
            $data["rma_increment_id"]       = $rmaItem->getIncrementId();
            $data["event_date"]             = $rmaItem->getCreatedAt();
            $data["sku"]                    = $rmaItem->getVendorSimpleSku();
            $data["reason"]                 = $rmaItem->getItemConditionName();
            $data["payment_method"]         = ucfirst(str_replace('_', ' ', $po->ghapiPaymentMethod()));
            $data["payment_channel_owner"]  = $po->getPaymentChannelOwner();
            $data["charge_commission_flag"] = $po->getChargeCommissionFlag();

            //if rma type is returned package and po was shipped using cod then return 100% of provision - assume that return value = sell value
            $data["approved_refund_amount"] = !$packageReturned ? $rmaItem->getReturnedValue() : $poItem->getFinalItemPrice();

            $data["price"]                  = $poItem->getPriceInclTax();       // Sprzedaż przed zniżką (zł)
            $data["discount_amount"]        = $poItem->getDiscountAmount();     // Zniżka (zł)
            $data["final_price"]            = $poItem->getFinalItemPrice();     // Sprzedaż w zł
            $data["commission_percent"]     = $rmaItem->getCommissionPercent(); // Stawka prowizji Modago;
            $data["gallery_discount_value"] = 0;
            foreach ($poItem->getDiscountInfo() as $relation) {
                /** @var Zolago_SalesRule_Model_Relation $relation */
                if ($relation->getPayer() == Zolago_SalesRule_Model_Rule_Payer::PAYER_GALLERY) {
                    $data["gallery_discount_value"] += floatval($relation->getDiscountAmount()); // Zniżka finansowana przez Modago
                }
            }
			if ($po->getChargeCommissionFlag()) { // Flaga czy naliczać prowizje w rozliczeniu?
				// (( <Sprzedaż przed zniżką> - <zniżka> + <Zniżka finansowana przez Modago>) * <Stawka prowizji Modago> ) * <podatek>
				$data["commission_value"] =
					(($data['price'] - $data["discount_amount"] + $data["gallery_discount_value"])
						* (floatval($data["commission_percent"]) / 100)) * self::getTax(); // Prowizja Modago
				$data["commission_value"] = round($data["commission_value"], 2, PHP_ROUND_HALF_UP);
			} else {
				$data["commission_value"] = 0;
			}
            $fraction = $data["approved_refund_amount"] / $data["final_price"]; // Procentowy udział
            $data['commission_return']      = round($data["commission_value"] * $fraction, 2, PHP_ROUND_HALF_UP);
            $data['discount_return']        = round($data["gallery_discount_value"] * $fraction, 2, PHP_ROUND_HALF_UP);

            // Korekta o rabaty finansowane przez Modago (procentowo)
            $galleryDiscountValue          += $data["discount_return"];
            $galleryCommissionValue        += $data["commission_return"];

            // (<Prowizja Modago> - <Zniżka finansowana przez Modago>) * procentowy zwrot z calosci
            $data["value"]                  = ($data["commission_value"] - $data["gallery_discount_value"]) * $fraction;
            $data["value"]                  = round($data['value'], 2, PHP_ROUND_HALF_UP);

            $commissionAmount              += $data["commission_value"];
            $amount                        += $data["value"];




            // Save
            $statementOrder = Mage::getModel('ghstatements/rma');
            $statementOrder->setData($data);
            $statementOrder->save();

            $rmaItem->setStatementId($statement->getId());
            $rmaItem->save();

            $rma = Mage::getModel('urma/rma')->load($rmaItem->getParentId());
            $rma->setStatementId($statement->getId());
            $rma->save();
        }

        $rmaStatementTotals->commissionAmount = $commissionAmount;
        $rmaStatementTotals->amount = $amount;
        $rmaStatementTotals->galleryDiscountValue = $galleryDiscountValue;
        $rmaStatementTotals->galleryCommissionValue = $galleryCommissionValue;
        return $rmaStatementTotals;
    }

    public static function processStatementsMarketing($statement) {
        $marketingStatementTotals = new stdClass();
        $marketingStatementValue = 0;

        $dateModel = Mage::getModel('core/date');
        $today     = $dateModel->date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today))) . ' 23:59:59';

        if ($statement->getForceCustomDate()) {
            $yesterday = $statement->getForceCustomDate() . ' 23:59:59';
        }

        /** @var Gh_Marketing_Model_Marketing_Cost $marketingModel */
        $marketingModel = Mage::getModel('ghmarketing/marketing_cost');

        /** @var Gh_Marketing_Model_Resource_Marketing_Cost_Collection $collection */
        $collection = $marketingModel->getCollection();
        $collection
        ->addFieldToFilter('statement_id',array('null' => true))
        ->addFieldToFilter('date',array('lteq' => $yesterday))
        ->addFieldToFilter('vendor_id',$statement->getVendorId())
        ->getSelect()->join(
            'gh_marketing_cost_type',
            'main_table.type_id = gh_marketing_cost_type.marketing_cost_type_id',
            array('gh_marketing_cost_type.name as type_name')
        );

        $marketingCostStatementData = array();

        if($collection->getSize()) {
            $vendorCpcCommision = $statement->getVendor()->getCpcCommission();
            $products = array();

            foreach($collection as $marketingCost) {
                /** @var Gh_Marketing_Model_Marketing_Cost $marketingCost */

                $marketingCost
                ->setBillingCost(
                    round(($marketingCost->getCost() + ($marketingCost->getCost() * ($vendorCpcCommision / 100))), 2, PHP_ROUND_HALF_UP)
                )
                ->setStatementId($statement->getId())
                ->save();

                $productId = $marketingCost->getProductId();
                if(!isset($products[$productId])) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $products[$productId] = $product;
                } else {
                    $product = $products[$productId];
                }

                $marketingCostStatementData[] = array(
                                                    'statement_id'              => $statement->getId(),
                                                    'product_id'                => $marketingCost->getProductId(),
                                                    'product_sku'               => $product->getSku(),
                                                    'product_vendor_sku'        => $product->getSkuv(),
                                                    'product_name'              => $product->getName(),
                                                    'marketing_cost_type_id'    => $marketingCost->getTypeId(),
                                                    'marketing_cost_type_name'  => $marketingCost->getTypeName(),
                                                    'date'                      => $marketingCost->getDate(),
                                                    'value'                     => $marketingCost->getBillingCost()
                                                );

                $marketingStatementValue += $marketingCost->getBillingCost();
            }
        }

        if(count($marketingCostStatementData)) {
            /** @var Gh_Statements_Model_Marketing $marketingStatementModel */
            $marketingStatementModel = Mage::getModel('ghstatements/marketing');
            /** @var GH_Statements_Model_Resource_Marketing $marketingStatementsResource */
            $marketingStatementsResource = $marketingStatementModel->getResource();
            $marketingStatementsResource->appendMarketings($marketingCostStatementData);
        }

        $marketingStatementTotals->amount = $marketingStatementValue;

        return $marketingStatementTotals;
    }
    
    /**
     * calculate invoice correction
     */

    public static function processStatementsInvoice($statement) {
        $invoiceStatementTotals = new stdClass();
        $invoiceStatementValue = array(
            'commission_correction' => 0,
            'delivery_correction' 	=> 0,
            'marketing_correction' 	=> 0,
        );

        $dateModel = Mage::getModel('core/date');
        $today     = $dateModel->date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today))) . ' 23:59:59';

        if ($statement->getForceCustomDate()) {
            $yesterday = $statement->getForceCustomDate() . ' 23:59:59';
        }
        $model = Mage::getModel('zolagopayment/vendor_invoice');
        $collection = $model->getCollection();
        $collection
        ->addFieldToFilter('statement_id',array('null' => true))
        ->addFieldToFilter('date',array('lteq' => $yesterday))
        ->addFieldToFilter('vendor_id',$statement->getVendorId())
        ->addFieldToFilter('wfirma_invoice_id',array('neq' => '0'))
        ->addFieldToFilter('is_invoice_correction',1);
        
        
        if($collection->getSize()) {

            foreach($collection as $vendorInvoice) {
                /** @var Zolago_Payment_Model_Vendor_Payment $vendorPayment */
                $vendorInvoice->setData('statement_id',$statement->getId());
                $vendorInvoice->save();

                $invoiceStatementValue['commission_correction'] += $vendorInvoice->getCommissionBrutto();
                $invoiceStatementValue['delivery_correction'] += $vendorInvoice->getTransportBrutto();
                $invoiceStatementValue['marketing_correction'] += $vendorInvoice->getMarketingBrutto();
            }
        }
        $invoiceStatementTotals->commissionCorrection = $invoiceStatementValue['commission_correction'];
        $invoiceStatementTotals->deliveryCorrection = $invoiceStatementValue['delivery_correction'];
        $invoiceStatementTotals->marketingCorrection = $invoiceStatementValue['marketing_correction'];
        return $invoiceStatementTotals;

    }
    public static function processStatementsPayment($statement) {
        $paymentStatementTotals = new stdClass();
        $paymentStatementValue = 0;

        $dateModel = Mage::getModel('core/date');
        $today     = $dateModel->date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today))) . ' 23:59:59';

        if ($statement->getForceCustomDate()) {
            $yesterday = $statement->getForceCustomDate() . ' 23:59:59';
        }

        /** @var Zolago_Payment_Model_Vendor_Payment $paymentModel */
        $paymentModel = Mage::getModel('zolagopayment/vendor_payment');

        /** @var Zolago_Payment_Model_Resource_Vendor_Payment_Collection $collection */
        $collection = $paymentModel->getCollection();
        $collection->addFieldToFilter('statement_id',array('null' => true))
            ->addFieldToFilter('date',array('lteq' => $yesterday))
            ->addFieldToFilter('vendor_id',$statement->getVendorId());

        if($collection->getSize()) {

            foreach($collection as $vendorPayment) {
                /** @var Zolago_Payment_Model_Vendor_Payment $vendorPayment */
                $vendorPayment->setData('statement_id',$statement->getId());
                $vendorPayment->save();

                $paymentStatementValue += $vendorPayment->getCost();
            }
        }

        $paymentStatementTotals->amount = $paymentStatementValue;

        return $paymentStatementTotals;
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
        return Mage::helper('ghstatements')->getTax();
    }

}