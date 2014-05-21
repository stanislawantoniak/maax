<?php
/**
 * pdf with aggregated orders
 */
class Zolago_Po_Model_Aggregated_Pdf extends Varien_Object {
    // Zend_Pdf
    protected $_doc;
    // Aggregation
    protected $_aggregated;
    
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
                    $page = $this->_doc->newPage(Zend_Pdf_Page::SIZE_A4);
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
                $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
                break;
            case 'i':
                $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
                break;
            default:
                $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');                                
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
        switch ($status) {
            case Zolago_Po_Model_Aggregated_Status::STATUS_CONFIRMED:
                return Mage::helper('zolagopo')->__('Confirmed');
            case Zolago_Po_Model_Aggregated_Status::STATUS_NOT_CONFIRMED:
                return Mage::helper('zolagopo')->__('Not confirmed');
            default:
                return Mage::helper('zolagopo')->__('Unknown');                
        }
    }
    protected function _prepareHeader($page,$num,$pages) {
        $aggr = $this->_aggregated;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.8));
        $page->drawRectangle(25,790,570,707);
        $this->_setFont($page,12);        
        $data = $aggr->getData();
        $vendor = $this->_prepareVendorData($data['vendor_id']);
        $pos = $this->_preparePosData($data['pos_id']);
        $status = $this->_prepareStatus($data['status']);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->drawText(Mage::helper('zolagopo')->__('Name').': '.$data['aggregated_name'],35,777,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Create date').': '.$data['created_at'],335,757,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Update date').': '.$data['updated_at'],335,737,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Vendor name').': '.$vendor,35,757,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('POS name').': '.$pos,35,737,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Status').': '.$status,35,717,'UTF-8');
        $page->drawText(Mage::helper('zolagopo')->__('Page').': '.$num.'/'.$pages,335,777,'UTF-8');
        
    }
}