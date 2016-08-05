<?php

class Snowdog_Freshmail_Block_Adminhtml_Newsletter_Subscriber_Grid
    extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
    /**
     * Prepare html for main grid buttons
     *
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->_getFreshmailButtonHtml();
        return $html;
    }

    /**
     * Create a FM sync button
     *
     * @return mixed
     */
    protected function _getFreshmailButtonHtml()
    {
        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('adminhtml')->__('Sync to FreshMail'),
                'onclick' => "setLocation('" . $this->_getSyncUrl() . "')",
                'class' => 'task'
            ))->toHtml();
    }

    /**
     * Retrieve a url to sync
     *
     * @return string
     */
    protected function _getSyncUrl()
    {
        return $this->getUrl('*/freshmail/sync');
    }
}
