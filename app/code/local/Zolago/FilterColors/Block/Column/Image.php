<?php

class Zolago_FilterColors_Block_Column_Image extends ManaPro_FilterColors_Block_Column_Image {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        ob_start();
        $cellId = $this->generateId();

        /* @var $files Mana_Core_Helper_Files */
        $imageUrl = '';
        $files = Mage::helper(strtolower('Mana_Core/Files'));
        if ($image = $row->getData($this->getColumn()->getIndex())) {
            $imageUrl = $files->getUrl($image, array('temp/image', 'image'));
        }
        ?>
        <div style="<?php //echo $this->_getStyle($row)?>"
             class="ct-container input-image <?php echo $this->getColumn()->getInlineCss() ?>"
             id="<?php echo $cellId ?>">&nbsp;
            <?php if(!empty($imageUrl)): ?>
            <img src="<?php echo $imageUrl; ?>" width="50px" />
            <?php endif; ?>
        </div>
        <?php
        $html = ob_get_clean();//.$this->renderCellOptions($cellId, $row);
        return $html;
    }
}