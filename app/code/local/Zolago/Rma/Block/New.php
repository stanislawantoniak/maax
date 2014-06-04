<?php
class Zolago_Rma_Block_New extends Mage_Core_Block_Template
{
    protected $_returnRenderer;
    public function getPo() {
        return Mage::registry('current_po');
    }
    public function getItemList() {
        $po = $this->getPo();
        if (!$po) {
            return array();
        }
        $items = $po->getItemsCollection();
        $out = array();
        $child = array();
        foreach ($items as $item) {
            if ($parentId = $item->getParentItemId()) {
                $child[$parentId][]  = $item;
            }
        }
        foreach ($items as $item) {
            $max = intval($item->getQty());
            if (!$item->getParentItemId()) {
                for ($a = 0; $a<$max; $a++) {
                    $entity_id = $item->getEntityId();
                    if (!empty($child[$item->getOrderItemId()])) {                        
                        $name = '';
                        foreach ($child[$item->getOrderItemId()] as $ch) {
                            $name .= $ch->getName();
                        }
                    } else {
                        $name = $item->getName();
                    }
                    $out[$entity_id][$a] = array (
                                               'entityId' => $entity_id,
                                               'name' => $name,
                                           );
                }
            }
        }
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
}
