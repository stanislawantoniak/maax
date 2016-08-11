<?php

/**
 * Class Zolago_Newsletter_Block_Subscribe
 */
class Zolago_Newsletter_Block_Subscribe extends Mage_Newsletter_Block_Subscribe {
    /**
     * Retrieve form action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new');
    }
}