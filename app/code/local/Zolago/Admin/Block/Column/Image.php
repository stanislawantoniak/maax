<?php

class Zolago_Admin_Block_Column_Image extends Mana_Admin_Block_Column_Image {
    public function render(Varien_Object $row) {
        Mage::log('Hello');
        ob_start();
        $cellId = $this->generateId();
        ?>

        <!--<div style="--><?php //echo $this->_getStyle($row)?><!--"-->
        <!--    class="ct-container input-image --><?php //echo $this->getColumn()->getInlineCss() ?><!--"-->
        <!--    id="--><?php //echo $cellId ?><!--">&nbsp;-->
        <!--</div>-->
        <?php
        $url = $this->getImageUrl($row);
        if (!empty($url)): ?>
            <img src="<?php echo $url; ?>" id="<?php echo $cellId ?>"/>
        <? endif; ?>
        <?php
        $html = ob_get_clean();//.$this->renderCellOptions($cellId, $row);
        return $html;
    }

    protected function getImageUrl($row){
        $files = Mage::helper(strtolower('Mana_Core/Files'));
        if ($image = $row->getData($this->getColumn()->getIndex())) {
            $image = $files->getUrl($image, array('temp/image', 'image'));
        }
        return $image;
    }
}