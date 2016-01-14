<?php

/**
 * Class GH_Statements_Helper_Vendor_Balance
 */
class GH_Statements_Helper_Vendor_Balance extends Mage_Core_Helper_Abstract
{
    /**
     * @param $startMonths
     * @return array
     */
    public function constructBalancePeriods($startMonths)
    {
        $result = array();

        foreach ($startMonths as $vendorId => $startMonthsItem) {

            $start = new DateTime(array_keys($startMonthsItem)[0]);
            $start->modify('first day of this month');
            $end = new DateTime(date("Y-m-d", time()));
            $end->modify('first day of next month');
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                $result[$vendorId][$dt->format("Y-m")] = $dt->format("Y-m");
            }
        }
        return $result;
    }

}