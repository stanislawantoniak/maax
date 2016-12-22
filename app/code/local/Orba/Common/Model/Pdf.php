<?php
/**
 * abstract model for pdf documents
 */
abstract class Orba_Common_Model_Pdf extends Varien_Object {
    // Zend_Pdf
    protected $_doc;

     /**
     * path to pdf files in media catalog
     * @return string;
     */

    abstract protected function _getFilePath();
     /**
     * prefix for pdf files
     * @return string;
     */
    abstract protected function _getFilePrefix();

    /**
     * preparing pdf
     * @param int $id
     */
     abstract protected function _preparePages($id);


    protected function _getFileName($id) {
        $sfx = $id % 100;
        $a = floor($sfx / 10);
        $b = $sfx % 10;
        $path = Mage::getBaseDir('media').DS.$this->_getFilePath().DS.$b.DS.$a.DS;
        if(!file_exists($path)) {
            mkdir($path,0755,true);
        }
        $filename = $path.$this->_getFilePrefix().$id.'.pdf';
        return $filename;
    }
    public function deleteFile($id) {
        if (file_exists($this->_getFileName($id))) {
            @unlink($this->_getFileName($id));
        }
    }
    public function getPdfFile($id) {
        if (!file_exists($this->_getFileName($id))) {
            $this->_preparePdf($id);
        }
        return $this->_getFileName($id);
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
     * adding new page to document
     * @param string $format
     * @return 
     */

    protected function _newPage($format = Zend_Pdf_Page::SIZE_A4) {	
        $page = $this->_doc->newPage($format);
        $this->_doc->pages[] = $page;
        return $page;           
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
    protected function _preparePdf($id) {
        $pdf = new Zend_Pdf();
        $this->_doc = $pdf;
        $this->_preparePages($id);
        $pdf->save($this->_getFileName($id));
    }
     protected function _setFont($page,$size = 7,$type = '') {
        switch ($type) {
        case 'barcode':
            $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/font/code128.ttf');
            break;
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

}