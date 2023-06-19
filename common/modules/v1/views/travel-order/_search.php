<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use dosamigos\switchery\Switchery;
use yii\web\JsExpression;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\TravelOrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="travel-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'TO_NO') ?>

    <?= $form->field($model, 'TO_subject') ?>

    <?= $form->field($model, 'date_from')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'yyyy-mm-dd'
        ],
        'pluginEvents' => [
            'changeDate' => "function(e) {
                const dateReceived = $('#travelorder-date_from');
                const dateActed = $('#travelorder-date_to-kvdate');
                dateActed.val('');
                dateActed.kvDatepicker('update', '');
                dateActed.kvDatepicker('setStartDate', dateReceived.val());
            }",
        ]
    ]); ?>

    <?= $form->field($model, 'date_to')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'yyyy-mm-dd'
        ],
    ]); ?>

    <?= $form->field($model, 'creatorName')->label('Created By') ?>

    <?= $form->field($model, 'date_filed')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'yyyy-mm-dd'
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
