<?php
/**
 * query grid renderer for column "Actions"
 */

class GH_Common_Block_Adminhtml_Rawsql_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        $urls = array();
        $urls[] = array(
                      "label"=>Mage::helper('ghcommon')->__('Edit'),
                      "url" => $this->getUrl('*/*/edit', array("id"=>$row->getId()))
                  );
        $urls[] = array(
                      "label"=>Mage::helper('ghcommon')->__('Launch'),
                      "url" => $this->getUrl('*/*/launch', array("back"=>"index", "id"=>$row->getId()))
                  );
        $urls[] = array(
            "label"=>Mage::helper('ghcommon')->__('Download CSV'),
            "url" => $this->getUrl('*/*/download',array('id' => $row->getId()))
        );
        $toImplode = array();
        foreach ($urls as $url) {
            $toImplode[] = '<a href="'.$url['url'].'">'.$this->escapeHtml($url['label']).'</a>';
        }
        return implode(" | ", $toImplode);

    }


}