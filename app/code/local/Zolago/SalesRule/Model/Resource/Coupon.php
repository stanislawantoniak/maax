<?php

class Zolago_SalesRule_Model_Resource_Coupon extends Mage_SalesRule_Model_Resource_Coupon
{

    /**
     * @param $insertData
     * @return $this
     * @throws Exception
     */
    public function bindCustomerToCoupon($insertData)
    {
        $this->beginTransaction();
        try {
            $insert = sprintf(
                "INSERT INTO %s (coupon_id, customer_id) VALUES %s "
                . " ON DUPLICATE KEY UPDATE customer_id=VALUES(customer_id)",
                $this->getMainTable(), $insertData
            );
            $this->_getWriteAdapter()->query($insert);

            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->rollBack();

            throw $e;
        }

        return $this;
    }
}