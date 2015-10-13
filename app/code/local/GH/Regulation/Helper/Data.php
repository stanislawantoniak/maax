<?php

/**
 * Class GH_Regulation_Helper_Data
 */
class GH_Regulation_Helper_Data extends Mage_Core_Helper_Abstract
{
    const REGULATION_DOCUMENT_FOLDER = "vendor_regulation";
    const REGULATION_DOCUMENT_ADMIN_FOLDER = "admin_regulation";

    const REGULATION_DOCUMENT_MAX_SIZE = 5; //MB

    /**
     * @return array
     */
    public static function getAllowedRegulationDocumentTypes()
    {
        return array("image/png", "image/jpg", "image/jpeg", "application/pdf");
    }

    /**
     * @param $file
     * @param $folder
     * @param $allowedRegulationDocumentTypes
     * @return array
     */
    public function saveRegulationDocument($file, $folder, $allowedRegulationDocumentTypes)
    {
        $_helper = Mage::helper("ghregulation");
        $result = array("status" => 0, "message" => "", "content" => array());

        $tmpName = $file["tmp_name"];
        $name = $file["name"];
        $type = $file["type"];
        $size = $file["size"];

        if (!in_array($type, $allowedRegulationDocumentTypes)) {
            $result = array("status" => 0, "message" => $_helper->__("File must be JPG, PNG or PDF"));
            return $result;
        }
        if (round($size / 1048576, 1) >= self::REGULATION_DOCUMENT_MAX_SIZE) { //5MB
            $result = array("status" => 0, "message" => $_helper->__("File too large. File must be less than %sMB.", GH_Regulation_Helper_Data::REGULATION_DOCUMENT_MAX_SIZE));
            return $result;
        }

        if (!empty($name)) {
            $newName = $this->cleanFileName($name);
            $image = md5($newName);
            $safeFolderPath = $image[0] . "/" . $image[1] . "/";

            @mkdir(Mage::getBaseDir('media') . DS . $folder . DS . $safeFolderPath, 0777, true);

            $path = Mage::getBaseDir('media') . DS . $folder . DS . $safeFolderPath . $newName;
            $result = array("status" => 1, "content" => array(
                "path"      => $path,
                "name"      => $name,
                "new_name"  => $newName));
            try {
                move_uploaded_file($tmpName, $path);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $result;
    }

    public function getVendorDocuments($vendor = 5,$idsOnly = false) { //todo: remove 5 - testing value
        if(!$vendor instanceof Zolago_Dropship_Model_Vendor) {
            $vendor = Mage::getModel('udropship/vendor')->load($vendor);
        }

        if(!$vendor || !$vendor->getId()) {
            return array();
        }

        $acceptDate = strtotime($vendor->getRegulationAcceptDocumentDate());
        $acceptDate = strtotime(date('Y-m-d',$acceptDate));

        /** @var GH_Regulation_Model_Regulation_Vendor_Kind $vendorKindModel */
        $vendorKindModel = Mage::getModel('ghregulation/regulation_vendor_kind');
        /** @var GH_Regulation_Model_Resource_Regulation_Vendor_Kind_Collection $vendorKindCollection */
        $vendorKindCollection = $vendorKindModel->getCollection();
        $vendorKindCollection
            ->addFieldToFilter('vendor_id',$vendor->getId())
            ->getSelect()
                ->join(
                    'gh_regulation_kind',
                    'main_table.regulation_kind_id = gh_regulation_kind.regulation_kind_id',
                    'gh_regulation_kind.name as regulation_kind_name');

        if($vendorKindCollection->getSize()) {
            $vendorKinds = array(); //gathers kinds assigned to vendor
            $kinds = array(); //gathers kinds names
            foreach($vendorKindCollection as $vendorKind) {
                $vendorKinds[] = $vendorKind->getRegulationKindId();
                $kinds[$vendorKind->getRegulationKindId()] = $vendorKind->getRegulationKindName();
            }
        } else {
            return array(); //vendor has no selected document kinds
        }

        /** @var GH_Regulation_Model_Regulation_Type $typeModel */
        $typeModel = Mage::getModel('ghregulation/regulation_type');
        /** @var GH_Regulation_Model_Resource_Regulation_Type_Collection $typeCollection */
        $typeCollection = $typeModel->getCollection();
        $typeCollection
            ->addFieldToFilter('regulation_kind_id',array('in'=>$vendorKinds))
            ->getSelect();

        if($typeCollection->getSize()) {
            $vendorPossibleTypes = array(); //gathers all types that vendor *COULD* be assigned to
            $typesKinds = array(); //gathers types to kinds connections
            foreach($typeCollection as $type) {
                $vendorPossibleTypes[] = $type->getRegulationTypeId();
                $typesKinds[$type->getRegulationTypeId()] = $type->getRegulationKindId();
            }
        } else {
            return array(); //there is no document types for selected document kinds
        }

        /** @var GH_Regulation_Model_Regulation_Document_Vendor $documentVendorModel */
        $documentVendorModel = Mage::getModel('ghregulation/regulation_document_vendor');
        /** @var GH_Regulation_Model_Resource_Regulation_Document_Vendor_Collection $documentVendorCollection */
        $documentVendorCollection = $documentVendorModel->getCollection();
        $documentVendorCollection
            ->addFieldToFilter('vendor_id',$vendor->getId())
            ->addFieldToFilter('regulation_type_id',array('in'=>$vendorPossibleTypes))
            ->setOrder('date',Zend_Db_Select::SQL_ASC);
        if($documentVendorCollection->getSize()) {
            $vendorFinalTypes = array();
            foreach($documentVendorCollection as $documentVendor) {
                $vendorFinalTypes[] = $documentVendor->getRegulationTypeId();
            }
            $vendorFinalTypes = array_unique($vendorFinalTypes);
        } else {
            return array(); //vendor has no assigned document types in admin
        }

        //get all documents of selected types
        /** @var GH_Regulation_Model_Regulation_Document $documentModel */
        $documentModel = Mage::getModel('ghregulation/regulation_document');
        /** @var GH_Regulation_Model_Resource_Regulation_Document_Collection $documentCollection */
        $documentCollection = $documentModel->getCollection();
        $documentCollection
            ->addFieldToFilter('regulation_type_id',array('in'=>$vendorFinalTypes))
            ->addFieldToFilter('date',array('lteq'=>date('Y-m-d')))
            ->setOrder('date',Zend_Db_Select::SQL_ASC);
        if($documentCollection->getSize()) {
            $preReturnDocuments = array(); //array of all possible documents that can be assigned to vendor
            $startDateForKinds = array();
            foreach($documentCollection as $document) {
                /** @var GH_Regulation_Model_Regulation_Document $document */
                $docArray = array(
                    'id' => $document->getId(),
                    'filename' => $document->getFileName(),
                    'url' => $document->getVendorUrl(),
                    'date' => $document->getDate()
                );
                $typeId = $document->getRegulationTypeId();
                $preReturnDocuments[$typesKinds[$typeId]][$document->getRegulationTypeId()][] = $docArray;

                $kindId = $typesKinds[$document->getRegulationTypeId()];
                if(!isset($startDateForKinds[$kindId]) || strtotime($document->getDate()) <= $acceptDate) {
                    $startDateForKinds[$kindId] = $document->getDate();
                }
            }
        } else {
            return array(); //there are no documents for selected document types
        }


        //timeframes holds start and end dates for each document type
        $timeframes = array();
        $lastkeys = array();
        foreach($documentVendorCollection as $k=>$documentVendor) {
            $typeId = $documentVendor->getRegulationTypeId();
            $kindId = $typesKinds[$typeId];
            $timeframes[$kindId][$k] = array(
                'type' => $typeId,
                'start' => ($k == 1 ? $startDateForKinds[$kindId] : $documentVendor->getDate()),
                'end' => date('Y-m-d')
            );
            if(isset($lastkeys[$kindId]) && isset($timeframes[$kindId][$lastkeys[$kindId]])) {
                $daybefore = date('Y-m-d',strtotime($documentVendor->getDate()) - 1);
                if($timeframes[$kindId][$lastkeys[$kindId]]['start'] > $daybefore) {
                    unset($timeframes[$kindId][$lastkeys[$kindId]]);
                } else {
                    $timeframes[$kindId][$lastkeys[$kindId]]['end'] = $daybefore;
                }
            }
            $lastkeys[$kindId] = $k;
        }

        //return proper documents for displaying in vendor history
        $returnDocuments = array(); //hold return documents in chronological order
        foreach ($timeframes as $kindId => $types) {
            $kindName = $kinds[$kindId];
            $returnDocuments[$kindName] = array();
            foreach($types as $typeTimeframe) {
                $tmpDocuments = array();
                foreach($preReturnDocuments[$kindId][$typeTimeframe['type']] as $finalDocument) {
                    if(strtotime($finalDocument['date']) <= strtotime($typeTimeframe['start'])) {
                        $tmpDocuments[0] = $finalDocument;
                        continue;
                    } elseif(strtotime($finalDocument['date']) <= strtotime($typeTimeframe['end'])) {
                        $tmpDocuments[] = $finalDocument;
                        continue;
                    }
                    break; //breaks loop because all documents for this timeframe has been assigned - no need to process next ones
                }
                $returnDocuments[$kindName] = array_merge($returnDocuments[$kindName],$tmpDocuments);
            }
            $returnDocuments[$kindName] = array_reverse($returnDocuments[$kindName]);
        }

        if($idsOnly) {
            $ids = array();
            foreach($returnDocuments as $kind=>$documents) {
                foreach($documents as $document) {
                    $ids[] = $document['id'];
                }
            }
        }

        if(isset($ids)) {
            return $ids;
        } else {
            return $returnDocuments;
        }
    }

    /**
     * Clean file name
     * @param $string
     * @return mixed
     */
    public static function cleanFileName($string)
    {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^.A-Za-z0-9\-\_]/', '', $string); // Removes special chars.
        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        return preg_replace('/_+/', '_', $string); // Replaces multiple underscores with single one.
    }

    /**
     * Return url for file uploaded by vendor on regulation acceptation steep
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
}