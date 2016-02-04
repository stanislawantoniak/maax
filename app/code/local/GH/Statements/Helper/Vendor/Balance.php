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

	/**
	 * Retrieve config corresponding dotpay_id to payment_channel_owner for all stores
	 * returned array like
	 * array(
	 * 	[<owner>] => array ('id_1', [...])
	 * 	[<owner>] => array ('id_2', [...])
	 * )
	 * Note: for owner see Zolago_Payment_Model_Source_Channel_Owner
	 *
	 * @return array
	 */
	public function getDotpaysPaymentChannelOwnerForStores() {
		$stores = Mage::app()->getStores();
		$config = array();
		foreach ($stores as $store) {
			/** @var Mage_Core_Model_Store $store */
			$dotpayId = $store->getConfig('payment/dotpay/id');
			$owner    = $store->getConfig('payment/dotpay/channel_owner');
			$config[$owner][] = $dotpayId;
		}
		foreach ($config as &$ids) {
			$ids = array_unique($ids);
		}
		return $config;
	}
}