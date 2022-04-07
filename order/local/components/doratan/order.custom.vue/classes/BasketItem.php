<?php
namespace Local\Sale;

class BasketItem extends \Bitrix\Sale\BasketItem
{

    protected $arImage = null;

    public function getPicture()
    {
        /**
         * @var \Local\Sale\Basket $basket
         */
        $basket = $this->getCollection();
        $arProduct = $basket->getProductData($this->getProductId());
        if (!empty($arProduct)) {    ///  не работает ??? изменить
            $image = new \Local\Lib\Parts\Image([]);
            $image->addImage($arProduct['DETAIL_PICTURE']);
            $image->addImage($arProduct['PREVIEW_PICTURE']);
            $this->arImage = $image->getFirstOriginal();
        }

        if (!is_array($this->arImage)) {
            $this->arImage = [];
        }

        return $this->arImage;
    }


    public function getPictureResized($arSizes)
    {
        if (!is_array($this->arImage)) {
            $this->getPicture();
        }

        $arImage = [];
        if (!empty($this->arImage)) {
            $image = new \Local\Lib\Parts\Image($arSizes);
            $image->addImage($this->arImage);
            $arImage = $image->getFirstResized();
        }
        return $arImage;
    }

}