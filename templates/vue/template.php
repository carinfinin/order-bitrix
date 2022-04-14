<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();?>
    <?
    foreach ($component->order->getBasket() as $item):
        echo '<pre>';
       echo $item->getField('NAME');
       echo $item->getQuantity();
        echo '</pre>';

    endforeach; ?>



<main id="app">
    <div class="region">
        <h3>Регион</h3>
        <input-form  :label="'Выберите город из списка'"></input-form>
    </div>
    <div class="delivery">
        <h3>Доставка</h3>
        <input-form :type="'radio'"></input-form>
    </div>
    <div class="pay_system">
        <h3>Тип оплаты</h3>
        <input-form :type="'radio'"></input-form>
    </div>
</main>

<?
$this->addExternalJs("https://unpkg.com/vue@next");
$this->addExternalJs("/local/components/doratan/order.custom.vue/templates/vue/main.js");
$this->addExternalJs("/local/components/doratan/order.custom.vue/templates/vue/input.js");
?>
<script>
    app.mount('#app');
</script>

