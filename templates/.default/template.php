<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var customOrderComponent $component */
?>

<table>
    <?
    print_r($component->getPriceDelivery());

    $counter = 1;
    foreach ($component->order->getBasket() as $item):
        /**
         * @var $item \Local\Sale\BasketItem
         */
        ?>
        <tr class="basket-data__tr">
            <td class="basket-data__td basket-data__td-number">
                <span class="basket-data__number"><?= $counter++ ?></span>
            </td>
            <td class="basket-data__td basket-data__td-img">
<!--                --><?//
//                if (!empty($item->getPicture())): ?>
<!--                    <img src="--><?//= $item->getPictureResized(['width' => 200, 'height' => 200])['SRC'] ?><!--" class="basket-data__img" alt="">-->
<!--                --><?// endif; ?>
            </td>
            <td class="basket-data__td">
                <span class="basket-data__product-title"><?= $item->getField('NAME') ?></span>
            </td>
            <td class="basket-data__td">
				<span class="basket-data__count-products">
					<?= $item->getQuantity() ?>
                    <?= $item->getField('MEASURE_NAME') ?>
				</span>
            </td>
            <td class="basket-data__td">
				<span class="basket-data__product-price">
					<?= \SaleFormatCurrency(
                        $item->getQuantity() * $item->getPrice(),
                        $item->getCurrency()
                    ) ?>
				</span>
            </td>
        </tr>
    <? endforeach; ?>

    <!--    <form action="">-->
    <!--        --><?// foreach ($component->order->getPropertyCollection() as $prop):
    //            /** @var \Bitrix\Sale\PropertyValue $prop */
    //            ?>
    <!--            <label>-->
    <!--                --><?//=$prop->getName()?><!--<br>-->
    <!--                <input type="text" name="--><?//= $prop->getField('CODE') ?><!--" value="--><?//= $prop->getValue() ?><!--">-->
    <!--            </label>-->
    <!--            <br>-->
    <!---->
    <!--        --><?// endforeach; ?>
    <!--    </form>-->


    <?$locationProp = $component->order
        ->getPropertyCollection()
        ->getDeliveryLocation();

    if (is_object($locationProp)):
        ?>
        <div class="check__content-row">
            <div class="check__content-label">
                <?= $locationProp->getName() ?>:
            </div>
            <div class="check__content-value">
                <a href="javascript:;" class="check__content-link">
                    <?= $locationProp->getViewHtml() ?>
                </a>
            </div>
        </div>
    <? endif; ?>


    <?$addressProp = $component->getPropByCode('ADDRESS');
if (is_object($addressProp)):
	?>
	<div class="check__content-row">
		<div class="check__content-label">
			<?= $addressProp->getName() ?>:
		</div>
		<div class="check__content-value">
			<a href="javascript:;" class="check__content-link">
				<?= $addressProp->getViewHtml() ?>
			</a>
		</div>
	</div>
<? endif; ?>

    <div class="check__content-row">
        <div class="check__content-label">
            <?= $component->getPropDataByCode('PHONE')['NAME'] ?>:
        </div>
        <div class="check__content-value">
            <a href="javascript:;" class="check__content-link">
                <?= $component->getPropDataByCode('PHONE')['VALUE'] ?>
            </a>
        </div>
    </div>



    <?echo \SaleFormatCurrency(
        $component->order->getPrice(),
        $component->order->getCurrency()
    ) ?>

    <br>


    <?echo \SaleFormatCurrency(
        $component->order->getDeliveryPrice(),
        $component->order->getCurrency()
    ) ?>

   <br>
   <br>

    <?$shipment = false;
    /** @var \Bitrix\Sale\Shipment $shipmentItem */
    foreach ($component->order->getShipmentCollection() as $shipmentItem) {
        if (!$shipmentItem->isSystem()) {
            $shipment = $shipmentItem;
            break;
        }
    }
    if ($shipment) :
        ?>
        <?= $shipment->getDelivery()->getName() ?>
    <? else:?>
        Самовывоз
    <? endif; ?>
    <br>


<? foreach ($component->order->getPaymentCollection() as $payment):
    /**
     * @var \Bitrix\Sale\Payment $payment
     */
    ?>
    <?= $payment->getPaymentSystemName() ?>
<? endforeach; ?>

    Выберите службу доставки:<br>
    <?foreach ($component->order->getAvailableDeliveries() as $obDelivery):?>
        <label>
            <input type="radio" name="delivery_id" value=<?=$obDelivery->getId()?>>
            <?=$obDelivery->getName()?>
        </label>
    <? endforeach; ?>

    Выберите платежную систему:<br>
    <?foreach ($component->order->getAvailablePaySystems() as $arPaySystem):?>
        <label>
            <input type="radio" name="payment_id" value=<?=$arPaySystem['ID']?>>
            <?=$arPaySystem['NAME']?>
        </label>
    <? endforeach; ?>
