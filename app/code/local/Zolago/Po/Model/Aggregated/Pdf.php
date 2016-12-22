<?php
/**
 * pdf with aggregated orders
 */
class Zolago_Po_Model_Aggregated_Pdf extends Orba_Common_Model_Pdf {
    const PO_AGGREGATED_PATH = 'shipping';
    const PO_AGGREGATED_PREFIX = 'aggregate_';
    const PO_AGGREGATED_ON_PAGE = 13;
    // Aggregation
    protected $_aggregated;
    protected $_line_count = 1;
    protected $_rows = array (
                           1 => 60,
                           2 => 180,
                           3 => 380,
                           4 => 435,
                           5 => 700,
                           6 => 760,
                       );
    // footer data
    protected $_totals = array (
                             'cod' => 0,
                             'palets' => 0,
                             'k' => 0,
                             'dr' => 0,
                             'dhl09' => 0,
                             'dhl12' => 0,
                         );


    public function setAggregated($aggr) {
        $this->_aggregated = $aggr;
    }
    public function _getFilePrefix() {
        return self::PO_AGGREGATED_PREFIX;
    }
    public function _getFilePath() {
        return self::PO_AGGREGATED_PATH;
    }
    protected function _drawBarcodes($page,$tracks,$counter) {        
        $rel = 475-30*$counter;
        $nubmers = array();
        $num = count($tracks)-1;
        $pos_tmp = $rel-20+$num*6;
        foreach ($tracks as $track) {
            $page->saveGS();
            $page->clipRectangle($this->_rows[1],$pos_tmp-10,$this->_rows[2],$pos_tmp+18);
            $number = $track->getNumber();
            $center = ($this->_rows[2] - $this->_rows[1])/2 - (strlen($number)*6)/2+$this->_rows[1];
            $page->drawText($number,$center,$pos_tmp-8,'UTF-8');
            $this->_setFont($page,36,'barcode');
            $page->drawText($number,$center-20,$pos_tmp+2);
            $this->_setFont($page,9);
            $pos_tmp -= 10;
            $page->restoreGS();
        }
    }
    protected function _addShip($ship,$po,$page,$counter) {
        $this->_setFont($page,9);
        $rel = 475-30*$counter;
        // draw barcodes
        $tracks = $ship->getTracksCollection();
        $this->_drawBarcodes($page,$tracks,$counter);
        $this->_drawCells($page,$rel,$rel-30);
        $page->drawText($this->_line_count++,40,$rel-20,'UTF-8');
        // tracking numbers
        // europalets
        $page->drawText('0',400,$rel-20,'UTF-8');
        // shipment address
        $id = $po->getShippingAddressId();
        $address = Mage::getModel('sales/order_address')->load($id);
        $data = $address->getData();
        // name
        $keys = array (
                    'firstname',
                    'middlename',
                    'lastname',
                );
        $text = $this->_prepareText($data,$keys);
        $data['fullname'] = $text;
        $text = $this->_prepareText($data,array('fullname','company'),', ');
        $this->_setFont($page,7);
        $page->drawText($text,440,$rel-9,'UTF-8');
        // address
        $text = $this->_prepareText($data,array('postcode','city'));
        $data['full_city'] = $text;
        $text = $this->_prepareText($data,array('full_city','street','telephone'),', ');
        $page->drawText($text,440,$rel-18,'UTF-8');
        // cod and insurance
        $data = array ();
        $value = $ship->getTotalValue() + $ship->getBaseTaxAmount() + $ship->getShippingAmount();
        if ($ship->getOrder()->getPayment()->getMethod() == 'cashondelivery') {
            $data['cod'] = 'COD';
            $cod_value = $value;
        } else {
            $cod_value = 0;
        }
        $data['insurance'] = Mage::helper('zolagopo')->__('INS decl. val. %.2f',$value);
        $text = $this->_prepareText($data,array('cod','insurance'),', ');
        $page->drawText($text,440,$rel-27,'UTF-8');
        // elements
        $weight = $ship->getTotalWeight();
        $pack = $ship->getPackages();
        $count = count($pack)? count($pack):1;
        $text = $this->_calculateElements($weight,$count);
        $page->drawText($text,190,$rel-15,'UTF-8');
        // COD
        $this->_setFont($page,9,'b');
        $page->drawText(sprintf('%.2f',$cod_value),710,$rel-20,'UTF-8');
        $this->_totals['cod'] += $cod_value;
        // send
        $this->_setFont($page,9);
        $page->drawText('Y',780,$rel-20,'UTF-8');
    }
    protected function _calculateElements($weight,$count) {
        $pattern = 'AH: %s - %.0f';
        $total_key = 'k';
        if ($weight < 5) {
            $class = 'k1';
        }
        elseif ($weight < 10) {
            $class = 'k2';
        }
        elseif ($weight < 20) {
            $class = 'k3';
        }
        elseif ($weight < 31.5) {
            $class = 'k4';
        }
        else {
            $class = $count;
            $count = $weight;
            $total_key = 'dr';
            $pattern = sprintf('AH: DR - %%s %s %%.2f kg',Mage::helper('zolagopo')->__('Total weight:'));
        }
        $this->_totals[$total_key] += $count;
        return sprintf($pattern,$class,$count);
    }
    protected function _prepareFooter($page,$counter) {
        $rel = 475-30*$counter;
        // totals
        $this->_setFont($page,9,'b');
        $sum = $this->_totals['k'] +
               $this->_totals['dr'] +
               $this->_totals['dhl09'] +
               $this->_totals['dhl12'];
        $page->drawText(Mage::helper('zolagopo')->__('Shipment count: %d',$sum),35,$rel-18,'UTF-8');
        $this->_setFont($page,9);
        $page->drawText(Mage::helper('zolagopo')->__('Items under 31,5 kg: %d',$this->_totals['k']),35,$rel-30,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('DR items: %d',$this->_totals['dr']),35,$rel-42,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('COD value: %.2f',$this->_totals['cod']),35,$rel-54,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Europalets: %d',$this->_totals['palets']),35,$rel-66,'UTF-8');
        // ah
        $this->_setFont($page,9,'b');
        $page->drawText(Mage::helper('zolagopo')->__('AH'),235,$rel-18,'UTF-8');
        $this->_setFont($page,9);
        $page->drawText(Mage::helper('zolagopo')->__('Shipment count: %d',$this->_totals['k']+$this->_totals['dr']),235,$rel-30,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Items under 31,5 kg: %d',$this->_totals['k']),235,$rel-42,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('DR items: %d',$this->_totals['dr']),235,$rel-54,'UTF-8');
        // dhl9
        $this->_setFont($page,9,'b');
        $page->drawText(Mage::helper('zolagopo')->__('DHL 09'),450,$rel-18,'UTF-8');
        $this->_setFont($page,9);
        $page->drawText(Mage::helper('zolagopo')->__('Shipment count: %d',$this->_totals['dhl09']),450,$rel-30,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('DHL09 items: %d',$this->_totals['dhl09']),450,$rel-42,'UTF-8');
        // dhl 12
        $this->_setFont($page,9,'b');
        $page->drawText(Mage::helper('zolagopo')->__('DHL 12'),635,$rel-18,'UTF-8');
        $this->_setFont($page,9);
        $page->drawText(Mage::helper('zolagopo')->__('Shipment count: %d',$this->_totals['dhl12']),635,$rel-30,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('DHL12 items: %d',$this->_totals['dhl12']),635,$rel-42,'UTF-8');
        // signs
        $page->setLineWidth(1);
        $page->setLineDashingPattern(array(2, 1, 2, 1), 1.6);
        $page->drawLine(45,$rel-98,210,$rel-98);
//        $page->drawText(Mage::helper('zolagopo')->__('Customer signature'),305,$rel-110,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('DHL signature'),45,$rel-110,'UTF-8');
        $page->drawLine(305,$rel-98,500,$rel-98);
        $page->drawText(Mage::helper('zolagopo')->__('Sender signature'),305,$rel-110,'UTF-8');

    }
    // preparing date from - to
    // additionally gets courier name
    protected function _prepareDate($collection) {
        foreach ($collection as $po) {
            $shipmentCollection = $po->getShipmentsCollection();
            foreach ($shipmentCollection as $ship) {
                if ($ship->getUdropshipStatus() != ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_CANCELED) {
                    $date = $ship->getCreatedAt();
                    if (!isset($this->_totals['date_start']) ||
                            ($date < $this->_totals['date_start'])) {
                        $this->_totals['date_start'] = $date;

                    }
                    if (!isset($this->_totals['date_end']) ||
                            ($date > $this->_totals['date_end'])) {
                        $this->_totals['date_end'] = $date;

                    }
                }

            }
        }
        if (isset($ship)) {
            $track = $ship->getTracksCollection()->getFirstItem();
            $this->_totals['courier'] = $track->getTitle();
        }
    }
    protected function _preparePages($id) {
        if (!$this->_aggregated) {
            $aggr = Mage::getModel('zolagopo/aggregated')->load($id);
            $this->setAggregated($aggr);
        }
        $aggr = $this->_aggregated;
        $id = $aggr->getId();
        if ($id) {
            $collection = Mage::getModel('udpo/po')->getCollection();
            $collection->addFieldToFilter('aggregated_id',$id);
            $count =  count($collection);
            $pages = ceil(($count+6)/(self::PO_AGGREGATED_ON_PAGE+1));
            $counter = 0;
            $num = 0;
            $this->_prepareDate($collection);
            foreach ($collection as $po) {
                if (!$counter) {
                    $num ++;
                    $page = $this->_doc->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
                    $this->_doc->pages[] = $page;
                    $this->_prepareHeader($page,$num,$pages);
                }
                $shipmentCollection = $po->getShipmentsCollection();
                foreach ($shipmentCollection as $ship) {
                    if ($ship->getUdropshipStatus() != ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_CANCELED) {
                        $this->_addShip($ship,$po,$page,$counter);
                        if ($counter++>self::PO_AGGREGATED_ON_PAGE) {
                            $counter = 0;
                        }
                    }
                }
            }
            if ($counter>10) {
                $page = $this->_doc->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
                $this->_prepareHeader($page,$num,$pages);
                $counter = 0; // next page
                $this->_doc->pages[] = $page;
            }
            // footer
            $this->_prepareFooter($page,$counter);
        }

    }
    protected function _prepareVendorData($vendor_id) {
        $out = '';
        if ($vendor_id) {
            $vendor = Mage::getModel('udropship/vendor')->load($vendor_id);
            if ($vendor) {
                $out = $vendor->getData();
            }
        }
        return $out;
    }
    protected function _preparePosData($pos_id) {
        $out = array();
        if ($pos_id) {
            $pos = Mage::getModel('zolagopos/pos')->load($pos_id);
            if ($pos) {
                $out = $pos->getData();
            }
        }
        return $out;
    }
    protected function _prepareStatus($status_id) {
        switch ($status_id) {
        case Zolago_Po_Model_Aggregated_Status::STATUS_CONFIRMED:
            return Mage::helper('zolagopo')->__('Confirmed');
        case Zolago_Po_Model_Aggregated_Status::STATUS_NOT_CONFIRMED:
            return Mage::helper('zolagopo')->__('Not confirmed');
        default:
            return Mage::helper('zolagopo')->__('Unknown');
        }
    }
    protected function _drawCells($page,$top,$bottom) {
        for ($a=1; $a<7; $a++) {
            $page->drawLine($this->_rows[$a],$top,$this->_rows[$a],$bottom);
        }
        $page->drawLine(35,$bottom,810,$bottom);
    }
    protected function _prepareHeader($page,$num,$pages) {
        $aggr = $this->_aggregated;
        $data = $aggr->getData();
        $created_at = $data['created_at'];
        $vendor = $this->_prepareVendorData($data['vendor_id']);
        $pos = $this->_preparePosData($data['pos_id']);
        $status = $this->_prepareStatus($data['status']);
        $date_start = date('Y-m-d',strtotime($this->_totals['date_start']));
        $date_end = date('Y-m-d',strtotime($this->_totals['date_end']));
        if (Mage::getSingleton('udropship/session')->isOperatorMode()) {
            $operator = Mage::getSingleton('udropship/session')->getOperator()->getEmail();
        } else {
            $operator = Mage::getSingleton('udropship/session')->getVendor()->getEmail();
        }
        $id_ecas = empty($pos['dhl_ecas'])? $vendor['dhl_ecas']:$pos['dhl_ecas'];
        $terminal = empty($pos['dhl_terminal'])? $vendor['dhl_terminal']:$pos['dhl_terminal'];

        $sap = $pos['dhl_account'];
        // only one courier
        $courier = $this->_totals['courier'];
        $phone = empty($pos['phone'])? '':$pos['phone'];
        $pos['full_code'] = $this->_prepareText($pos,array('postcode','city'));
        $address = $this->_prepareText($pos,array('full_code','street'),', ');
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFont($page,12,'b');
        $page->drawText(Mage::helper('zolagopo')->__('Dispatch list of shipments created from %s to %s',$date_start,$date_end),100,550,'UTF-8');

        $this->_setFont($page,10,'b');
        $page->drawText(Mage::helper('zolagopo')->__(
                            'Sender: %s',
                            $this->_prepareText($pos,array('name','company'),', ')),
                        35,
                        530,
                        'UTF-8'
                       );
        $this->_setFont($page,9);
        $page->drawText(Mage::helper('zolagopo')->__('Page %s from %s',$num,$pages),700,540,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Date and time of create: %s',$created_at),35,515,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Address :%s',$address),35,500,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Operator eCas: %s',$operator),285,515,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Id eCas: %s',$id_ecas),550,515,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('SAP: %s',$sap),700,515,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Courier: %s',$courier),455,515,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Phone: %s',$phone),550,500,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Terminal: %s',$terminal),700,500,'UTF-8');

        // table header
        $page->drawLine(35,495,810,495);
        $page->drawText(Mage::helper('zolagopo')->__('No.'),35,480,'UTF-8');
        $page->drawLine(35,478,810,478);
        $this->_drawCells($page,495,475);

        $page->drawText(Mage::helper('zolagopo')->__('Tracking number'),70,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Shipment items'),235,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Europalets'),385,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Receiver | Additional services | Value'),485,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('COD'),715,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Sent'),770,480,'UTF-8');

    }
}