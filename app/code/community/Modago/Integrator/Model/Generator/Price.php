<?php

/**
 * generating price file
 */
class Modago_Integrator_Model_Generator_Price
    extends Modago_Integrator_Model_Generator
{
    protected $_getList;
    protected $_header;
    protected $_footer;

    protected function _construct()
    {
        $this->setFileNamePrefix('PRICES');
    }

    /**
     * prepare content
     *
     * @return array
     */
    protected function _prepareList()
    {
        if ($this->_getList) {
            return false;
        }
        $collection = Mage::getModel("catalog/product")->getCollection();
        $collection->addAttributeToSelect("price");
        $collection->addAttributeToSelect("special_price");

        //$collection->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        //$collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);


        $res = array();
        foreach($collection as $collectionItem){
            $res[] = array("sku" => $collectionItem->getSku(), "price" => $collectionItem->getPrice());
        }

        $this->_getList = true;
        return $res;
    }

    /**
     * prepare xml block
     *
     * @param array $item
     * @return string
     */
    protected function _prepareXmlBlock($item)
    {
        $price = number_format($item['price'], 2);
        return '<product price="' . $price . '">' . $item['sku'] . '</product>';
    }

    /**
     * prepare header
     *
     * @return string
     */
    protected function _getHeader()
    {
        if (!$this->_header) {
            $this->_header = '<mall><merchant>' . $this->getExternalId() . '</merchant><priceList priceId="A">';
        }
        return $this->_header;
    }

    /**
     * prepare footer
     *
     * @return string
     */
    protected function _getFooter()
    {
        if (!$this->_footer) {
            $this->_footer = "</priceList></mall>";
        }
        return $this->_footer;
    }


}
