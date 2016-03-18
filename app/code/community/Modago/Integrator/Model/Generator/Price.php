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
        $this->setFileNamePrefix('prices');
    }

    /**
     * prepare content
     *
     * @return array
     */
    public function prepareList()
    {
        if ($this->_getList) {
            return false;
        }
        $res = array();

        Mage::getModel("modagointegrator/product_price")
               ->appendPricesForConfigurable($res);

        Mage::getModel("modagointegrator/product_price")
               ->appendPricesForSimple($res);

        $this->_getList = true;
        return $res;
    }

    /**
     * prepare xml block
     *
     * @param array $item
     * @return string
     */
    public function prepareXmlBlock($key,$items)
    {        
        $out = $this->_getSectionHeader($key);
        foreach ($items as $item) {
            $out .= $this->_prepareXmlBlockItem($item);
        }
        $out .= $this->_getSectionFooter();
        return $out;
    }
    
    /**
     * block for one price item
     * @param array $item
     * @return string
     */

    protected function _prepareXmlBlockItem($item) {
        $price = number_format($item['price'], 2, ".", "");
        return '<product price="' . $price . '">' . $item['sku'] . '</product>';
    }

    /**
     * prepare header
     *
     * @return string
     */
    public function getHeader()
    {
        if (!$this->_header) {
            $this->_header = '<mall><version>'.$this->getHelper()->getModuleVersion().
                '</version><merchant>' . $this->getExternalId() . '</merchant>';
        }
        return $this->_header;
    }

    /**
     * prepare footer
     *
     * @return string
     */
    public function getFooter()
    {
        if (!$this->_footer) {
            $this->_footer = "</mall>";
        }
        return $this->_footer;
    }

    /**
     * prepare section header
     *
     * @param $type
     * @return string
     */
    protected function _getSectionHeader($type)
    {
        return '<priceList priceId="' . $type . '">';
    }

    /**
     * prepare section footer
     *
     * @return string
     */
    protected function _getSectionFooter()
    {
        return "</priceList>";
    }

}
