<?php
/**
  
 */
interface ZolagoOs_OmniChannel_Model_Label_Interface_Type
{
    public function updateTrack($track, $labelImage);

    public function renderTracks($tracks);
    
    public function printBatch($batch=null);

    public function printTrack($track=null);
}