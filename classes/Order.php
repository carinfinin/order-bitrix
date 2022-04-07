<?php
namespace Local\Sale;

use Bitrix\Sale\Delivery;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;

class Order extends \Bitrix\Sale\Order
{

    public function getAvailableDeliveries()
    {
        $shipment = false;
        /** @var \Bitrix\Sale\Shipment $shipmentItem */
        foreach ($this->getShipmentCollection() as $shipmentItem) {
            if (!$shipmentItem->isSystem()) {
                $shipment = $shipmentItem;
                break;
            }
        }

        $availableDeliveries = [];
        if (!empty($shipment)) {
            $availableDeliveries = Delivery\Services\Manager::getRestrictedObjectsList($shipment);

            foreach ($availableDeliveries as $obDelivery) {
                if($obDelivery->isCalculatePriceImmediately()) {
                    $shipment->setField('DELIVERY_ID', $obDelivery->getId());
                    $calcResult = $obDelivery->calculate();

                    if ($calcResult->isSuccess()) {
                        echo $calcResult->getPrice();
                        echo $calcResult->getPeriodDescription();
                    } else {
                        //нет созданых отгрузок
                    }
                }
            }
        }

        return $availableDeliveries;
    }

    public function getAvailablePaySystems()
    {
        $payment = Payment::create($this->getPaymentCollection());
        $payment->setField('SUM', $this->getPrice());
        $payment->setField("CURRENCY", $this->getCurrency());
        $paySystemsList = PaySystem\Manager::getListWithRestrictions($payment);

        //logo
//        foreach ($paySystemsList as $key => $paySystem) {
//            if (intval($paySystem['LOGOTIP']) > 0) {
//                $paySystemsList[$key]['LOGO_PATH'] = \Local\Lib\Helpers\Files::getOriginal(
//                    $paySystem['LOGOTIP']
//                );
//            }
//        }

        return $paySystemsList;
    }

}