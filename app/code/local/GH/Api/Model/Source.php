<?php


class GH_Api_Model_Source extends Mage_Core_Model_Abstract
{
    /**
     * @return array
     */
    public function toOptionHash($selector = false)
    {
        $hlp = Mage::helper('ghapi');
        $options = array();

        switch ($this->getPath()) {
            case 'yesno':
                $options = array(
                    1 => $hlp->__('Yes'),
                    0 => $hlp->__('No'),
                );
                break;
            default:
                Mage::throwException($hlp->__('Invalid request for source options: ' . $this->getPath()));
        }

        if ($selector) {
            $options = array('' => $hlp->__('* Please select')) + $options;
        }

        return $options;
    }
}
