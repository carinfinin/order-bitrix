<?php
namespace Local\Sale;

use Bitrix\Main\Loader;

class Basket extends \Bitrix\Sale\Basket
{
    protected $productsList = [];

    protected function getAllProductsData()
    {
        if(Loader::includeModule('iblock')) {
            $productIDs = [];
            foreach ($this->collection as $item) {
                /**
                 * @var $item \Local\Sale\BasketItem
                 */
                $productIDs[$item->getProductId()] = $item->getProductId();
            }
            sort($productIDs);

            if(!empty($productIDs)) {
                $arFilter = [
                    'ID' => $productIDs
                ];

                $rsElements = \CIBlockElement::GetList([], $arFilter);

                while ($arElement = $rsElements->Fetch()) {
                    $this->productsList[$arElement['ID']] = $arElement;
                }

                foreach ($productIDs as $id) {
                    if (!isset($this->productsList[$id])) {
                        $this->productsList[$id] = [];
                    }
                }
            }
        }
    }

    public function getProductData($productId)
    {
        $data = [];

        if (!isset($this->productsList[$productId])) {
            $this->getAllProductsData();
        }

        if (isset($this->productsList[$productId])) {
            $data = $this->productsList[$productId];
        }

        return $data;
    }
}