<?php

/**
 * Class Zolago_Sales_Model_Order_Payment_Transaction
 */
class Zolago_Sales_Model_Order_Payment_Transaction extends Mage_Sales_Model_Order_Payment_Transaction {

    /**
     * @param array $data
     * @return array
     */
    public function validate($data = null)
    {
        if ($data === null) {
            $data = $this->getData();
        } elseif ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        if (!is_array($data)) {
            return false;
        }

        $errors = $this->getValidator()->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;

    }
}