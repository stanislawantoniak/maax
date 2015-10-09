<?php

/**
 * Class GH_Regulation_Helper_Data
 */
class GH_Regulation_Helper_Data extends Mage_Core_Helper_Abstract
{
    const REGULATION_DOCUMENT_FOLDER = "vendor_regulation";

    public function getAllowedRegulationDocumentTypes()
    {
        return array("image/png", "image/jpg", "application/pdf");
    }
}