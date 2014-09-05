<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 05.09.2014
 */ 
class Zolago_Image_Helper_Data extends Mage_Catalog_Helper_Image
{
    /**
     * Crop position
     *
     * @var string
     */
    protected $_cropPosition;

    /**
     * Adpative resize flag
     *
     * @var bool
     */
    protected $_scheduleAdaptiveResize = false;

    /**
     * Reset all previos data
     *
     * @return Zolago_Image_Helper_Image
     */
    protected function _reset()
    {
        $this->_scheduleAdaptiveResize = false;
        $this->_cropPosition = 0;
        parent::_reset();
    }

    /**
     * Adaptive resize method
     *
     * @param int      $width  image width
     * @param int|null $height image height
     *
     * @return \Zolago_Image_Helper_Image
     */
    public function adaptiveResize($width, $height = null)
    {
        $this->_getModel()
            ->setWidth($width)
            ->setHeight((!is_null($height)) ? $height : $width)
            ->setKeepAspectRatio(true)
            ->setKeepFrame(false)
            ->setConstrainOnly(false);
        ;
        $this->_scheduleAdaptiveResize = true;
        return $this;
    }

    /**
     * Set crop bosition
     *
     * @param string $position top, bottom or center
     *
     * @return \Zolago_Image_Helper_Image
     */
    public function setCropPosition($position)
    {
        $this->_cropPosition = $position;
        return $this;
    }

    /**
     * Return generated image url
     *
     * @return string
     */
    public function __toString()
    {
        try {
            if ($this->getImageFile()) {
                $this->_getModel()->setBaseFile($this->getImageFile());
            } else {
                $this->_getModel()->setBaseFile($this->getProduct()
                    ->getData($this->_getModel()->getDestinationSubdir()));
            }

            if ($this->_getModel()->isCached()) {
                return $this->_getModel()->getUrl();
            } else {
                if ($this->_scheduleRotate) {
                    $this->_getModel()->rotate($this->getAngle());
                }

                if ($this->_cropPosition) {
                    $this->_getModel()->setCropPosition($this->_cropPosition);
                }

                if ($this->_scheduleResize) {
                    $this->_getModel()->resize();
                }

                if ($this->_scheduleAdaptiveResize) {
                    $this->_getModel()->adaptiveResize();
                }

                if ($this->getWatermark()) {
                    $this->_getModel()->setWatermark($this->getWatermark());
                }

                $url = $this->_getModel()->saveFile()->getUrl();
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        }
        return $url;
    }

    /**
     * Init Image processor model
     *
     * Rewrited to change model
     *
     * @param Mage_Catalog_Model_Product $product       product
     * @param string                     $attributeName attribute name
     * @param string                     $imageFile     image file name
     *
     * @return \Zolago_Image_Helper_Image
     */
    public function init(Mage_Catalog_Model_Product $product, $attributeName, $imageFile = null)
    {
        $this->_reset();
        $this->_setModel(Mage::getModel('zolago_image/catalog_product_image'));
        $this->_getModel()->setDestinationSubdir($attributeName);
        $this->setProduct($product);
        $subdir = $this->_getModel()->getDestinationSubdir();
        $this->setWatermark(Mage::getStoreConfig("design/watermark/{$subdir}_image"));
        $this->setWatermarkImageOpacity(Mage::getStoreConfig("design/watermark/{$subdir}_imageOpacity"));
        $this->setWatermarkPosition(Mage::getStoreConfig("design/watermark/{$subdir}_position"));
        $this->setWatermarkSize(Mage::getStoreConfig("design/watermark/{$subdir}_size"));

        if ($imageFile) {
            $this->setImageFile($imageFile);
        } else {
            // add for work original size
            $this->_getModel()->setBaseFile($this->getProduct()->getData($subdir));
        }
        return $this;
    }
}