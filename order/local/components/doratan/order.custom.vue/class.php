<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
require_once (__DIR__ . '/classes/Basket.php');
require_once (__DIR__ . '/classes/BasketItem.php');
require_once (__DIR__ . '/classes/Order.php');

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Sale\Registry;

class customOrderComponent extends CBitrixComponent
{
    /**
     * @var \Bitrix\Sale\Order
     */
    public $order;

    public $propMap = [];

    protected $errors = [];

    function __construct($component = null)
    {
        parent::__construct($component);

        if(!Loader::includeModule('sale')){
            $this->errors[] = 'No sale module';
        };

        if(!Loader::includeModule('catalog')){
            $this->errors[] = 'No catalog module';
        };
    }

    function onPrepareComponentParams($arParams)
    {
        if (isset($arParams['PERSON_TYPE_ID']) && intval($arParams['PERSON_TYPE_ID']) > 0) {
            $arParams['PERSON_TYPE_ID'] = intval($arParams['PERSON_TYPE_ID']);
        } else {
            if (intval($this->request['payer']['person_type_id']) > 0) {
                $arParams['PERSON_TYPE_ID'] = intval($this->request['payer']['person_type_id']);
            } else {
                $arParams['PERSON_TYPE_ID'] = 1;
            }
        }

        if (isset($arParams['IS_AJAX'])
            && ($arParams['IS_AJAX'] == 'Y' || $arParams['IS_AJAX'] == 'N')) {
            $arParams['IS_AJAX'] = $arParams['IS_AJAX'] == 'Y';
        } else {
            if (
                isset($this->request['is_ajax'])
                && ($this->request['is_ajax'] == 'Y' || $this->request['is_ajax'] == 'N')
            ) {
                $arParams['IS_AJAX'] = $this->request['is_ajax'] == 'Y';
            } else {
                $arParams['IS_AJAX'] = false;
            }
        }

        return $arParams;
    }

    protected function createVirtualOrder()
    {
        global $USER;

        // помена крзины
        $registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
        $registry->set(Registry::ENTITY_BASKET, '\Local\Sale\Basket');
        $registry->set(Registry::ENTITY_BASKET_ITEM, '\Local\Sale\BasketItem');

        // помена класса заказа
        $registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
        $registry->set(Registry::ENTITY_ORDER, '\Local\Sale\Order');

        try {


            $siteId = \Bitrix\Main\Context::getCurrent()->getSite();
            $basketItems = \Bitrix\Sale\Basket::loadItemsForFUser(
                \CSaleBasket::GetBasketUserID(),
                $siteId
            )
                ->getOrderableItems();

            if (count($basketItems) == 0) {
                LocalRedirect(PATH_TO_BASKET);
            }

            $this->order = \Bitrix\Sale\Order::create($siteId, $USER->GetID());
            $this->order->setPersonTypeId($this->arParams['PERSON_TYPE_ID']);
            $this->order->setBasket($basketItems);

            $this->setOrderProps();

//            добавляе отнрузки

            /* @var $shipmentCollection \Bitrix\Sale\ShipmentCollection */
            $shipmentCollection = $this->order->getShipmentCollection();

            if (intval($this->request['delivery_id']) > 0) {
                $shipment = $shipmentCollection->createItem(
                    Bitrix\Sale\Delivery\Services\Manager::getObjectById(
                        intval($this->request['delivery_id'])
                    )
                );
            } else {
                $shipment = $shipmentCollection->createItem();
            }

            /** @var $shipmentItemCollection \Bitrix\Sale\ShipmentItemCollection */
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            $shipment->setField('CURRENCY', $this->order->getCurrency());

            foreach ($this->order->getBasket()->getOrderableItems() as $item) {
                /**
                 * @var $item \Bitrix\Sale\BasketItem
                 * @var $shipmentItem \Bitrix\Sale\ShipmentItem
                 * @var $item \Bitrix\Sale\BasketItem
                 */
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }


//            добавляе системы оплаты
            if (intval($this->request['payment_id']) > 0) {
                $paymentCollection = $this->order->getPaymentCollection();
                $payment = $paymentCollection->createItem(
                    Bitrix\Sale\PaySystem\Manager::getObjectById(
                        intval($this->request['payment_id'])
                    )
                );
                $payment->setField("SUM", $this->order->getPrice());
                $payment->setField("CURRENCY", $this->order->getCurrency());
            }
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }
    public function getPropByCode($code)
    {
        $result = false;

        $propId = 0;
        if (isset($this->propMap[$code])) {
            $propId = $this->propMap[$code];
        }

        if ($propId > 0) {
            $result = $this->order
                ->getPropertyCollection()
                ->getItemByOrderPropertyId($propId);
        }

        return $result;
    }

    public function getPropDataByCode($code)
    {
        $result = [];

        $propId = 0;
        if (isset($this->propMap[$code])) {
            $propId = $this->propMap[$code];
        }

        if ($propId > 0) {
            $result = $this->order
                ->getPropertyCollection()
                ->getItemByOrderPropertyId($propId)
                ->getFieldValues();
        }

        return $result;
    }

    protected function setOrderProps()
    {
        global $USER;
        $arUser = $USER->GetByID(intval($USER->GetID()))
            ->Fetch();



        if (is_array($arUser)) {
            $fio = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
            $fio = trim($fio);
            $arUser['FIO'] = $fio;
        }


        foreach ($this->order->getPropertyCollection() as $prop) {
            /** @var \Bitrix\Sale\PropertyValue $prop */
            $this->propMap[$prop->getField('CODE')] = $prop->getPropertyId();
            $value = '';

            switch ($prop->getField('CODE')) {
                case 'FIO':
                    $value = $this->request['contact']['family'];
                    $value .= ' ' . $this->request['contact']['name'];
                    $value .= ' ' . $this->request['contact']['second_name'];

                    $value = trim($value);
                    if (empty($value)) {
                        $value = $arUser['FIO'];
                    }
                    break;

                default:
            }

            if (empty($value)) {
                foreach ($this->request as $key => $val) {
                    if (strtolower($key) == strtolower($prop->getField('CODE'))) {
                        $value = $val;
                    }
                }
            }

            if (empty($value)) {
                $value = $prop->getProperty()['DEFAULT_VALUE'];
            }

            if (!empty($value)) {
                $prop->setValue($value);
            }
        }
    }

    function executeComponent()
    {
        global $APPLICATION;

        if ($this->arParams['IS_AJAX']) {
            $APPLICATION->RestartBuffer();
        }

        $this->createVirtualOrder();

        if (isset($this->request['save']) && $this->request['save'] == 'Y') {
            $this->order->save();
        }

        if ($this->arParams['IS_AJAX']) {
            if ($this->getTemplateName() != '') {
                ob_start();
                $this->includeComponentTemplate();
                $this->arResponse['html'] = ob_get_contents();
                ob_end_clean();
            }

            $this->arResponse['errors'] = $this->errors;

            header('Content-Type: application/json');
            echo json_encode($this->arResponse);
            $APPLICATION->FinalActions();
            die();
        } else {
            $this->includeComponentTemplate();
        }
    }

}