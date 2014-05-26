<?php
/**
 * pdf with aggregated orders
 */
class Zolago_Po_Model_Aggregated_Pdf extends Varien_Object {
    // Zend_Pdf
    protected $_doc;
    // Aggregation
    protected $_aggregated;
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
    
    public function getPdf() {
        if (empty($this->_doc)) {
            $this->_preparePdf();
        }    
        return $this->_doc;
    }    
    protected function _addPo($po,$page,$counter) {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $rel = 55*$counter;
        $page->drawRectangle(25,700+$rel,570,650+55*$rel);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFont($page,8);        
        $data = $po->getData();
        $page->drawText(Mage::helper('zolagopo')->__('Number').': '.$data['increment_id'],35,690+$rel,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Date').': '.$data['increment_id'],35,680+$rel,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Value').': '.$data['increment_id'],35,670+$rel,'UTF-8');
        
    }
    protected function _preparePages() {
        $agg = $this->_aggregated;
        $id = $agg->getId();
        if ($id) {
            $collection = Mage::getModel('udpo/po')->getCollection();
            $collection->addFieldToFilter('aggregated_id',$id);
            $count =  count($collection);
            $pages = ceil($count/10);
            $counter = 0;
            $num = 0;
            foreach ($collection as $po) {
                if (!$counter) {
                    $num ++;
                    $page = $this->_doc->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
                    $this->_prepareHeader($page,$num,$pages);
                    $this->_doc->pages[] = $page;
                }
                $this->_addPo($po,$page,$counter);
                if ($counter++>9) {
                    $counter = 0;
                }                
            }
        }        
        
    }
    protected function _preparePdf() {
        $pdf = new Zend_Pdf();
        $this->_doc = $pdf;
        $this->_preparePages();
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
        $out = '';
        if ($pos_id) {
            $pos = Mage::getModel('zolagopos/pos')->load($pos_id);
            if ($pos) {
                $out = $pos->getName();
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
            $page->drawLine($this->_rows[$a],495,$this->_rows[$a],475);
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
        $phone = '';
        $terminal = '';
        $address = '';
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFont($page,12,'b');
        $page->drawText(Mage::helper('zolagopo')->__('Proof of postage from %s to %s',$date_start,$date_end),100,550,'UTF-8');

        $this->_setFont($page,10,'b');
        $page->drawText(Mage::helper('zolagopo')->__('Sender'),35,530,'UTF-8');
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
        $page->drawText(Mage::helper('zolagopo')->__('COD'),755,480,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Sended'),585,480,'UTF-8');
        
    }
}