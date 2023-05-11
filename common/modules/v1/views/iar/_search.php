<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\IarSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="iar-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'iar_no') ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'iar_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ],
            ]) ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'pr_id') ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'po_id') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'invoice_no') ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'invoice_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ],
            ]) ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'inspectorName') ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'date_inspected')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-xs-12">
            &nbsp;
        </div>
        <div class="col-md-3 col-xs-12">
            &nbsp;
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'receiverName') ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'date_received')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ],
            ]) ?>
        </div>
    </div>

    <div class="form-group pull-right">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Clear', ['class' => 'btn btn-outline-secondary', 'onClick' => 'redirectPage()']) ?>
    </div>

    <div class="clearfix"></div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$script = '
    function redirectPage()
    {
        window.location.href = "'.Url::to(['/v1/iar/']).'";
    }
';
$this->registerJs($script, View::POS_END);
?>