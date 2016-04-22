<?php

class ZolagoOs_Rma_Model_Label_Pdf extends ZolagoOs_OmniChannel_Model_Label_Pdf
{
    public function getBatchFileName($batch)
    {
        $filename = 'label_batch-'.$batch->getId().'.pdf';
        if ($batch->getForcedFilename()) {
            $filename = $batch->getForcedFilename().'.pdf';
        }
        return $filename;
    }
}