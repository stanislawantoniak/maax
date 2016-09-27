<?php

class Snowdog_Freshmail_Model_System_Config_Source_Lists
{
    /**
     * Get subscription list options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => '',
                'label' => Mage::helper('snowfreshmail')
                    ->__('-- Please Select --'),
            ),
        );

        foreach (Mage::helper('snowfreshmail/api')->getLists() as $list) {
            $options[] = array(
                'value' => $list['subscriberListHash'],
                'label' => $list['name'],
            );
        }

        return $options;
    }
}
