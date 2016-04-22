<?php
/**
  
 */

/**
* PDF label adapter
*
* Accepts following properties:
* - batch ZolagoOs_OmniChannel_Model_Label_Batch
* - vendor ZolagoOs_OmniChannel_Model_Vendor
*/
class ZolagoOs_OmniChannel_Model_Label_Epl
    extends ZolagoOs_OmniChannel_Model_Label_Abstract_Type
    implements ZolagoOs_OmniChannel_Model_Label_Interface_Type
{
    protected function _construct()
    {
        parent::_construct();
        $this->setContentType('application/octet-stream');
    }

    public function updateTrack($track, $labelImages)
    {
        $track->setLabelImage(join("\n", (array)$labelImages));
        $track->setLabelFormat('EPL');

        return $this;
    }

    public function renderTracks($tracks)
    {
        $epl = '';
        foreach ($tracks as $track) {
            if ($track->getLabelFormat()!='EPL') {
                continue;
            }
            $labels = explode("\n", $track->getLabelImage());
            foreach ($labels as $label) {
                $epl .= base64_decode($label);
            }
        }
        return $epl;
    }

    public function renderBatchContent($batch=null)
    {
        if (is_null($batch)) {
            $batch = $this->getBatch();
        } else {
            $this->setBatch($batch);
        }

        $this->setVendor($batch->getVendor());
        return array(
            'filename' => 'label_batch-'.$this->getBatch()->getId().'.epl',
            'content' => $this->renderTracks($this->getBatch()->getBatchTracks()),
            'type' => $this->getContentType(),
        );
    }

    public function renderTrackContent($track=null)
    {
        if (is_null($track)) {
            $track = $this->getTrack();
        } else {
            $this->setTrack($track);
        }

        $this->setVendor($this->_getTrackVendor($track));

        return array(
            'filename' => $track->getNumber().'.epl',
            'content' => $this->renderTracks(array($track)),
            'type' => $this->getContentType()
        );
    }
}