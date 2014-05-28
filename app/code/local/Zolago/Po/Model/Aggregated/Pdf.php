<?php
/**
 * pdf with aggregated orders
 */
class Zolago_Po_Model_Aggregated_Pdf extends Varien_Object {
    // Zend_Pdf
    protected $_doc;
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
    
    public function setAggregated($aggr) {
        $this->_aggregated = $aggr;
    }
    protected function _getFileName($id) {
        $sfx = $id % 100;
        $a = floor($sfx / 10);
        $b = $sfx % 10;
        $path = Mage::getBaseDir('media').DS.'shipping'.DS.$b.DS.$a.DS;
        if(!file_exists($path)) {
            mkdir($path,0755,true);
        }
        $filename = $path.'aggregate_'.$id.'.pdf';
        return $filename;
    }
    public function getPdf($id) {          
        if (empty($this->_doc)) {
            if (!file_exists($this->_getFileName($id))) {
                $this->_preparePdf($id);
            } else {
                return file_get_contents($this->_getFileName($id));
            }
        }    
        return $this->_doc->render();
    }    
    
    /**
     * connecting text array into one text line using keys
     */    
    protected function _prepareText($data, $keys,$separator = ' ') {
        $tmp = array();        
        foreach ($keys as $key) {
            if (!empty($data[$key])) {
                $tmp[] = $data[$key];
            }            
        }
        return implode($separator,$tmp); 
    }
    protected function _addShip($ship,$po,$page,$counter) {
        $this->_setFont($page,9);
        $rel = 475-30*$counter;
        $this->_drawCells($page,$rel,$rel-30);
        $page->drawText($this->_line_count++,40,$rel-20);
        $tracks = $ship->getTracksCollection();
        // tracking numbers
        $num = count($tracks)-1;
        $pos_tmp = $rel-20+$num*6;
        foreach ($tracks as $track) {
            $number = $track->getNumber();
            $center = ($this->_rows[2] - $this->_rows[1])/2 - (strlen($number)*6)/2+$this->_rows[1];
            $page->drawText($track->getNumber(),$center,$pos_tmp);
            $pos_tmp -= 10;               
        }
        // europalets
        $page->drawText('0',400,$rel-20);        
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
        $page->drawText($text,440,$rel-9);
        // address
        $text = $this->_prepareText($data,array('postcode','city'));
        $data['full_city'] = $text;
        $text = $this->_prepareText($data,array('full_city','street','telephone'),', ');
        $page->drawText($text,440,$rel-16);
    }
    protected function _preparePages() {
        $aggr = $this->_aggregated;
        $id = $aggr->getId();
        if ($id) {
            $collection = Mage::getModel('udpo/po')->getCollection();
            $collection->addFieldToFilter('aggregated_id',$id);
            $count =  count($collection);
            $pages = ceil($count/16);
            $counter = 0;
            $num = 0;
            foreach ($collection as $po) {
                if (!$counter) {
                    $num ++;
                    $page = $this->_doc->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
                    $this->_prepareHeader($page,$num,$pages);
                    $this->_doc->pages[] = $page;
                }
                $shipmentCollection = $po->getShipmentsCollection();
                foreach ($shipmentCollection as $ship) {
                    if ($ship->getUdropshipStatus() != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED) {
                        $this->_addShip($ship,$po,$page,$counter);
                        if ($counter++>15) {
                            $counter = 0;
                        }                
                    }
                }
            }
            // footer
        }        
        
    }
    protected function _preparePdf($id) {
        if (!$this->_aggregated) {
    		$aggr = Mage::getModel('zolagopo/aggregated')->load($id);
	    	$this->setAggregated($aggr);
        }
        $pdf = new Zend_Pdf();
        $this->_doc = $pdf;
        $this->_preparePages();
        $pdf->save($this->_getFileName($id));
    }
    protected function _setFont($page,$size = 7,$type = '') {
        switch ($type) {
            case 'b':
                $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/font/Arial_Bold.ttf');                                
//                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD); 
                
//                $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
                break;
            case 'i':
                $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/font/Arial_Italic.ttf');                                
//                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC); 
//                $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
                break;
            default:
                $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/font/Arial.ttf');                                
//                $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');                                
//                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA); 

        }
        $page->setFont($font,$size);
    }
    protected function _prepareVendorData($vendor_id) {
        $out = '';
        if ($vendor_id) {
            $vendor = Mage::getModel('udropship/vendor')->load($vendor_id);
            if ($vendor) {
                $out = $vendor->getVendorName();
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
        for ($a=1;$a<7;$a++) {
            $page->drawLine($this->_rows[$a],$top,$this->_rows[$a],$bottom);
        }
        $page->drawLine(35,$bottom,810,$bottom);
    }
    protected function _prepareHeader($page,$num,$pages) {
        $aggr = $this->_aggregated;
        $data = $aggr->getData();
        $vendor = $this->_prepareVendorData($data['vendor_id']);
        $pos = $this->_preparePosData($data['pos_id']);
        $status = $this->_prepareStatus($data['status']);
        $date_start = date('Y-m-d');
        $date_end = date('Y-m-d');
        $operator = '';
        $id_ecas = '';
        $sap = '';
        $courier = '';
        $phone = empty($pos['phone'])? '':$pos['phone'];
        $terminal = '';
        $pos['full_code'] = $this->_prepareText($pos,array('postcode','city'));
        $address = $this->_prepareText($pos,array('full_code','street'),', ');
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFont($page,12,'b');
        $page->drawText(Mage::helper('zolagopo')->__('Proof of postage from %s to %s',$date_start,$date_end),100,550,'UTF-8');

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
        $page->drawText(Mage::helper('zolagopo')->__('Date and time of create'),35,515,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Address :%s',$address),35,500,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Operator eCas: %s',$operator),305,515,'UTF-8');
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
        
        $page->drawText(Mage::helper('zolagopo')->__('Tracking number'),75,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Elements of shipping'),235,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Europalets'),385,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Receiver | Additional services | Value'),485,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('COD'),715,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Sended'),770,480,'UTF-8');
        
    }
}