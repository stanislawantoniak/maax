<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Model_Source extends ZolagoOs_OmniChannel_Model_Source_Abstract
{
    const UDQA_STATUS_DECLINED = -1;
    const UDQA_STATUS_PENDING  = 0;
    const UDQA_STATUS_APPROVED = 1;

    const UDQA_VISIBILITY_PRIVATE = 0;
    const UDQA_VISIBILITY_PUBLIC  = 1;

    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udqa');

        $options = array();

        switch ($this->getPath()) {

            case 'zosqa/general/default_question_status':
            case 'zosqa/general/default_answer_status':
            case 'statuses':
                $options = array(
                    self::UDQA_STATUS_PENDING  => $hlp->__('Pending'),
                    self::UDQA_STATUS_APPROVED => $hlp->__('Approved'),
                    self::UDQA_STATUS_DECLINED => $hlp->__('Declined'),
                );
                break;

            case 'visibility':
                $options = array(
                    self::UDQA_VISIBILITY_PRIVATE => $hlp->__('Private'),
                    self::UDQA_VISIBILITY_PUBLIC  => $hlp->__('Public'),
                );
                break;

            default:
                Mage::throwException($hlp->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>$hlp->__('* Please select')) + $options;
        }

        return $options;
    }
}