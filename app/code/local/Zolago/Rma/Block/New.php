<?php
class Zolago_Rma_Block_New extends Mage_Core_Block_Template
{
    protected $_returnRenderer;
	/**
	 * @return Zolago_Po_Model_Po
	 */
    public function getPo() {
        return Mage::registry('current_po');
    }
    public function getItemList() {
        $po = $this->getPo();
        if (!$po) {
            return array();
        }
        $items = $po->getItemsCollection();
        $out = Mage::helper('zolagorma')->getItemList($items);
        return $out;
    }
    public function getReturnRenderer() {
        if (is_null($this->_returnRenderer)) {
            $helper = Mage::helper('urma');
            $list = $helper->getItemConditionTitles();
            $out = '';
            foreach ($list as $key=>$item) {
                $out .= '<option value="'.$key.'">'.Mage::helper('zolagorma')->__($item).'</option>';
            }
            $this->_returnRenderer = $out;
        }
        return $this->_returnRenderer;
    }
	public function getHours() {
		$opts = array();
		for($i=6*2;$i<16*2-1;$i++){
			$opts[$i] = sprintf("%02d:%02d", floor($i/2), ($i%2)*15);
		}
		return $opts;
	}
}
