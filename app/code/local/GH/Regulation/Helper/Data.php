<?php

/**
 * Class GH_Regulation_Helper_Data
 */
class GH_Regulation_Helper_Data extends Mage_Core_Helper_Abstract
{
    const REGULATION_DOCUMENT_FOLDER = "vendor_regulation";
    const REGULATION_DOCUMENT_ADMIN_FOLDER = "admin_regulation";

    const REGULATION_DOCUMENT_VENDOR_ROLE_SINGLE = 'single';
    const REGULATION_DOCUMENT_VENDOR_ROLE_PROXY  = 'proxy';

    /**
     * @return array
     */
    public static function getAllowedRegulationDocumentTypes()
    {
        return array("image/png", "image/jpg", "image/jpeg", "application/pdf");
    }

    /**
     * Return mime type from allowed types for specific $filename
     * NOTE: This is not validation function
     * @param string $filename
     * @return string
     */
    public function getMimeTypeFromFileName($filename) {
        $ext = substr($filename, -4);
        if ($ext == 'jpeg') {
            return 'image/jpeg';
        }
        $ext = substr($filename, -3);
        if ($ext == 'jpg') {
            return 'image/jpg';
        }
        if ($ext == 'png') {
            return 'image/png';
        }
        return 'application/pdf';
    }
    
    /**
     * Return file type calculated from file name
     *
     * @param string $name
     * @return string
     */

    public function getFileType($name) {
        $ext = strtoupper(pathinfo($name,PATHINFO_EXTENSION));
        if (preg_match('/^PDF$/',$ext)) return 'application/pdf';
        if (preg_match('/^JP(EG|G|E)$/',$ext)) return 'image/jpeg';
        if (preg_match('/^(X-)?PNG$/',$ext)) return 'image/png';
        if (preg_match('/^GIF$/',$ext)) return 'image/gif';
        if (preg_match('/^BMP$/',$ext)) return 'image/bmp';
        return 'other';
    }

    /**
     * Saving regulation document $file to specific $folder in media dir
     * Validation allow only $allowedRegulationDocumentTypes types
     * @see GH_Regulation_Helper_Data::getAllowedRegulationDocumentTypes()
     * Param $useRandom move saved file to 'random' dir
     *
     * @param $file
     * @param $folder
     * @param $allowedRegulationDocumentTypes
     * @param bool $useRandom
     * @return array
     */
    public function saveRegulationDocument($file, $folder, $allowedRegulationDocumentTypes, $useRandom = true)
    {
        $_helper = Mage::helper("ghregulation");
        /* @var $ghCommonHelper GH_Common_Helper_Data */
        $ghCommonHelper = Mage::helper('ghcommon');
        $result = array("status" => 0, "message" => "", "content" => array());

        $tmpName = $file["tmp_name"];
        $name = $file["name"];
        $size = $file["size"];
        
        $type = $this->getFileType($name);
        if (!in_array($type, $allowedRegulationDocumentTypes)) {
            $result = array("status" => 0, "message" => $_helper->__("File must be JPG, PNG or PDF"));
            return $result;
        }
        if ($size >= $ghCommonHelper->getMaxUploadFileSize()) { //5MB
            $result = array("status" => 0, "message" => $_helper->__("File too large. File must be less than %sMB.", round($ghCommonHelper->getMaxUploadFileSize() / (1024*1024), 1)));
            return $result;
        }

        if (!empty($name)) {
            $newName = GH_Common_Helper_Data::cleanFileName($name);
            $image = md5($newName . ($useRandom ? mt_rand() : ''));
            $safeFolderPath = $image[0] . "/" . $image[1] . "/";

            @mkdir(Mage::getBaseDir('media') . DS . $folder . DS . $safeFolderPath, 0777, true);

            $path = $folder . DS . $safeFolderPath . $newName;
            $fullPath = Mage::getBaseDir('media') . DS . $path;
            $result = array("status" => 1, "content" => array(
                "path" => $path,
                "full_path" => $fullPath,
                "name" => $name,
                "new_name" => $newName));
            try {
                move_uploaded_file($tmpName, $fullPath);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $result;
    }

    /**
     * gets all regulation documents for provided vendor
     * example output when $idsOnly = false (chronological order from newest to oldest, first in each kind is currently active):
     *  array (
     *      'Regulamin (document kind name)' => array(
     *          [0] => array (
     *              'type' => 2, (document type id)
     *              'filename' => "regulamin.pdf",
     *              'url' => "http://(...)", //GH_Regulation_Model_Document::getVendorUrl()
     *              'date' => "2015-10-25" //document start date
     *          ),
     *          [1] => array(...),
     *          [2] => array(...)
     *      )
     *      'Another document kind name' = array(...)
     *  );
     *
     * example output when $idsOnly = true (array of document ids valid for vendor):
     *  array(
     *      [0] => "12",
     *      [1] => "45",
     *      [2] => "3",
     *      [...] => ...
     *  );
     *
     * returns an empty array if no vendor documents can be returned
     *
     * @param Zolago_Dropship_Model_Vendor|int $vendor
     * @param bool $idsOnly
     * @return array
     */
    public function getVendorDocuments($vendor,$idsOnly = false) {
        if(!$vendor instanceof Zolago_Dropship_Model_Vendor) {
            $vendor = Mage::getModel('udropship/vendor')->load($vendor);
        }

        if(!$vendor || !$vendor->getId()) {
            return array();
        }

        if (!$acceptDate = strtotime($vendor->getRegulationAcceptDocumentDate())) {
            $acceptDate = Mage::getModel('core/date')->timestamp(time());
        }
        
        $acceptDate = date('Y-m-d',$acceptDate);
        $typeTable = Mage::getSingleton('core/resource')->getTableName('ghregulation/regulation_type');
        $kindTable = Mage::getSingleton('core/resource')->getTableName('ghregulation/regulation_kind');
        $documentTable = Mage::getSingleton('core/resource')->getTableName('ghregulation/regulation_document');
        
        // prepare array with assigned types
        $collection = Mage::getResourceModel('ghregulation/regulation_document_vendor_collection');
        $collection->getSelect()
            ->join (
                array('type' => $typeTable),
                'main_table.regulation_type_id = type.regulation_type_id',
                array()
            )
            -> join (
                array('kind' => $kindTable),
                'type.regulation_kind_id = kind.regulation_kind_id',
                array('kindname' => 'kind.name','kindid'=>'kind.regulation_kind_id')
            )
            ->order('type.regulation_kind_id')
            ->order('main_table.date','DESC')
            ->where('main_table.vendor_id=?',$vendor->getId())
            ->where('main_table.date<=?',Mage::getModel('core/date')->date('Y-m-d'));
        $assign = array();
        $types = array();
        foreach ($collection as $pos) {
            if (!isset($assign[$pos->getKindid()])) {
                $assign[$pos->getKindid()] = array();
            }
            if ($pos->getDate() < $acceptDate) {
                $pos->setDate($acceptDate);
            }
            $assign[$pos->getKindid()][] = $pos->getData();
            $types[$pos->getRegulationTypeId()] = $pos->getRegulationTypeId();
        }
        // prepare array with document versions
        
        $collection = Mage::getResourceModel('ghregulation/regulation_document_collection');
        $collection->addFieldToFilter('regulation_type_id',array('in'=>$types));
        $collection->addFieldToFilter('date',array('lteq' => Mage::getModel('core/date')->date('Y-m-d')));
        $collection->addOrder('date','ASC');
        $documents = array();
        foreach ($collection as $document) {
            $documents[$document->getRegulationTypeId()][] = array (
                'id' => $document->getId(),
                'filename' => $document->getFilename(),
                'url' => $document->getVendorUrl(),
                'date' => $document->getDate(),
            ); 
        }
        
        // calculate final table
        $final = array();
        foreach ($assign as $kindId => $typeset) {
            foreach ($typeset as $typeKey => $type) {
                $docTmp = empty($documents[$type['regulation_type_id']])? array(): $documents[$type['regulation_type_id']]; 
                $key = 0;
                // skip older documents
                while (isset($docTmp[$key]) && ($docTmp[$key]['date']<$type['date'])) {
                    $key ++;                    
                }
                       // get latest document element
                if (isset($docTmp[$key-1])) {
                    $tmp = $docTmp[$key-1];
                    $tmp['date'] = $type['date'];
                    $final[$type['kindname']][$tmp['id']] = $tmp;
                }
                // add same type documents
                $nextDate = isset($typeset[$typeKey+1])? $typeset[$typeKey+1]['date']: '9999-31-12';
                while (isset($docTmp[$key]) && ($docTmp[$key]['date']<$nextDate)) {
                    $tmp = $docTmp[$key++];
                    $final[$type['kindname']][$tmp['id'].'-'.uniqid()] = $tmp;
                }
            }
        }
        // Sorting chronological by date
        foreach ($final as $kindname => $arr) {
            usort($final[$kindname], function($a, $b) { return strtotime($b['date']) - strtotime($a['date']); });
        }
        if($idsOnly) {
            $ids = array();
            foreach($final as $kind=>$documents) {
                foreach($documents as $document) {
                    $ids[] = $document['id'];
                }
            }
        }

        if(isset($ids)) {
            return $ids;
        } else {
            return $final;
        }
        
        
    }



    /**
     * Return url for file uploaded by vendor on regulation acceptation steep
     * in udropship front
     *
     * @param $vendorId
     * @param $fileName
     * @param null $token
     * @return string
     */
    public function getVendorUploadedDocumentUrl($vendorId, $fileName, $token = null) {
        return Mage::getUrl('udropship/vendor/getVendorUploadedDocument', array(
            'vendor' => $vendorId,
            'file'   => $fileName,
            'key'    => $token
        ));
    }

    /**
     * Return url for file uploaded by vendor on regulation acceptation steep
     * in adminhtml
     *
     * @param $vendorId
     * @param $fileName
     * @return mixed
     */
    public function getVendorUploadedDocumentUrlForAdmin($vendorId, $fileName) {
        return Mage::helper("adminhtml")->getUrl("adminhtml/regulation/getVendorUploadedDocument", array(
            'vendor' => $vendorId,
            'file'   => $fileName
        ));
    }


    /**
     * @param $vendor
     * @return array
     */
    public function getDocumentsToAccept($vendor) {
        return Mage::getResourceModel("ghregulation/regulation_document")->getDocumentsToAccept($vendor);
    }
}