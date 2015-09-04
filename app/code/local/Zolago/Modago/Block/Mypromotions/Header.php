<?php

/**
 * block with list of promotions header
 */
class Zolago_Modago_Block_Mypromotions_Header extends Zolago_Modago_Block_Mypromotions
{

    protected function _prepareLayout()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            array(
                'label' => $this->__('Home Page'),
                'title' => $this->__('Home Page'),
                'link' => Mage::getBaseUrl()
            )
        );
        // add second item without link
        $breadcrumbs->addCrumb(
            'mypromotions',
            array(
                'label' => $this->__('Your promotion coupons'),
                'title' => $this->__('Your promotion coupons')
            )
        );

        parent::_prepareLayout();
    }

} 