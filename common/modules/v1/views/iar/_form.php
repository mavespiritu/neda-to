<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Iar */
/* @var $form yii\widgets\ActiveForm */
$posUrl = \yii\helpers\Url::to(['/v1/iar/po-list']);
?>

<div class="iar-form">

    <?php $form = ActiveForm::begin([
        'id' => 'iar-form',
    	'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>

    <?= $model->iarItems ? $form->field($model, 'pr_id')->widget(Select2::classname(), [
        'data' => $prs,
        'options' => ['placeholder' => 'Select PR','multiple' => false, 'disabled' => true, 'class'=>'pr-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        'pluginEvents' => [
            'select2:select'=>'
                function(){
                    $.ajax({
                        url: "'.$posUrl.'",
                        data: {
                            id: this.value
                        }
                    }).done(function(result) {
                        $(".po-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select PO", allowClear: true});
                        $(".po-select").select2("val","");
                    });
                }'

        ]
    ]) : $form->field($model, 'pr_id')->widget(Select2::classname(), [
        'data' => $prs,
        'options' => ['placeholder' => 'Select PR','multiple' => false, 'class'=>'pr-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        'pluginEvents' => [
            'select2:select'=>'
                function(){
                    $.ajax({
                        url: "'.$posUrl.'",
                        data: {
                            id: this.value
                        }
                    }).done(function(result) {
                        $(".po-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select PO", allowClear: true});
                        $(".po-select").select2("val","");
                    });
                }'

        ]
    ])?>

    <?= $model->iarItems ? $form->field($model, 'po_id')->widget(Select2::classname(), [
        'data' => $pos,
        'options' => ['placeholder' => 'Select PO','multiple' => false, 'disabled' => true, 'class'=>'po-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
    ]) : 
    $form->field($model, 'po_id')->widget(Select2::classname(), [
        'data' => $pos,
        'options' => ['placeholder' => 'Select PO','multiple' => false, 'class'=>'po-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
    ])?>

    <?= $form->field($model, 'iar_date')->widget(DatePicker::classname(), [
        'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
        ],
    ]) ?>

    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'invoice_no')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'invoice_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'inspected_by')->widget(Select2::classname(), [
                'data' => $inspectors,
                'options' => ['placeholder' => 'Select Staff','multiple' => false, 'class'=>'inspector-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
            ]) ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'date_inspected')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]) ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'received_by')->widget(Select2::classname(), [
                'data' => $supplyOfficers,
                'options' => ['placeholder' => 'Select Staff','multiple' => false, 'class'=>'receiver-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
            ]) ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'date_received')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
