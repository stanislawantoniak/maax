<?php

/**
 * Class Zolago_Banner_Model_Banner zolagobanner/banner
 * @method string getType()
 */
class Zolago_Banner_Model_Banner extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init("zolagobanner/banner");
    }

    /**
     * @param array $data
     * @return boolean|array
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

        $errors = Mage::getSingleton("zolagobanner/banner_validator")->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }


    public function saveBannerContent($content){
        $this->getResource()->saveBannerContent($content);
    }

    public function scaleImage($imagePath, $imageResizePath, $width, $height=null)
    {
        try
        {
            $image = new Varien_Image($imagePath);
            if(!is_null($height)) {
                $image->constrainOnly(false);
                $image->keepFrame(true);
                $image->backgroundColor(array(255, 255, 255));
            }
            $image->keepAspectRatio(true);
            $image->resize($width, $height);
            $image->save($imageResizePath);
        }
        catch(Exception $e)
        {
            Mage::logException($e);
        }
    }
}