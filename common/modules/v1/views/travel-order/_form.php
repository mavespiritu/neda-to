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
/* @var $model common\modules\v1\models\TravelOrder */
/* @var $form yii\widgets\ActiveForm */
$js = '
jQuery(".destination_wrapper").on("afterInsert", function(e, item) {
    $(".col-sm-9.col-sm-offset-3").removeClass("col-sm-9 col-sm-offset-3").addClass("col-md-12 col-xs-12");
    jQuery(".destination_wrapper .destination-counter").each(function(index) {
        jQuery(this).html((index + 1))
    });
    applySelect2();
    applyPluginEvents();
});

jQuery(".destination_wrapper").on("afterDelete", function(e, item) {
    jQuery(".destination_wrapper .destination-counter").each(function(index) {
        jQuery(this).html((index + 1))
    });
});

$(document).ready(function(){
    $(".col-sm-9.col-sm-offset-3").removeClass("col-sm-9 col-sm-offset-3").addClass("col-md-12 col-xs-12");
    applySelect2();
    applyPluginEvents();
});

function applySelect2() {
    $(".region-select").select2({
        allowClear: true,
        placeholder: "Select region",
        width: "100%",
        theme: "krajee",
    });

    $(".province-select").select2({
        allowClear: true,
        placeholder: "Select province",
        width: "100%",
        theme: "krajee",
    });

    $(".citymun-select").select2({
        allowClear: true,
        placeholder: "Select city/municipality",
        width: "100%",
        theme: "krajee",
    });
}

function applyPluginEvents() {
    $(".region-select").on("select2:select", function(e) {
        var selectElement = $(this);
        var provinceSelect = selectElement.closest(".destination").find(".province-select");
        var citymunSelect = selectElement.closest(".destination").find(".citymun-select");
        
        $.ajax({
            url: "'.Url::to(['/v1/travel-order/province-list']).'",
            data: { id: selectElement.val() },
            success: function(result) {
                provinceSelect.html("").select2({
                    data: result,
                    theme: "krajee",
                    width: "100%",
                    placeholder: "Select province",
                    allowClear: true
                });
                citymunSelect.html("").select2({
                    data: result,
                    theme: "krajee",
                    width: "100%",
                    placeholder: "Select city/municipality",
                    allowClear: true
                });
            }
        });
    });

    $(".province-select").on("select2:select", function(e) {
        var selectElement = $(this);
        var citymunSelect = selectElement.closest(".destination").find(".citymun-select");

        $.ajax({
            url: "'.Url::to(['/v1/travel-order/citymun-list']).'",
            data: { id: selectElement.val() },
            success: function(result) {
                citymunSelect.html("").select2({
                    data: result,
                    theme: "krajee",
                    width: "100%",
                    placeholder: "Select city/municipality",
                    allowClear: true
                });
            }
        });
    });
}
';

$this->registerJs($js);
?>


<div class="travel-order-form">

    <?php $form = ActiveForm::begin([
    	'options' => ['id' => 'travel-order-form', 'class' => 'disable-submit-buttons'],
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'wrapper' => 'col-sm-9',
            ],
        ],
    ]); ?>

    <div class="row">

        <div class="col-md-4 col-xs-12">

            <h4>I. Travel Information</h4>
            <br>

            <?= $form->field($model, 'type_of_travel')->widget(Select2::classname(), [
                'data' => $travelTypes,
                'options' => ['placeholder' => 'Select Travel Type','multiple' => false, 'class'=>'travel-type-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
            ]) ?>

            <?= $form->field($model, 'TO_subject')->textarea(['rows' => 3]) ?>

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

            <?= $form->field($model, 'withVehicle')->widget(Switchery::className(), [
                'options' => [
                    'label' => false,
                    'title' => 'Toggle if project is completed',
                ],
                'clientOptions' => [
                    'color' => '#6366F1',
                    'size' => 'small'
                ],
                'clientEvents' => [
                    'change' => new JsExpression('function() {
                        this.checked == true ? this.value = 1 : this.value = 0;
                    }'),
                ]
            ]) ?>

            <?= $form->field($model, 'otherpassenger')->textarea(['rows' => 3]) ?>

            <?= $form->field($model, 'othervehicle')->textarea(['rows' => 3]) ?>

            <?= $form->field($model, 'otherdriver')->textarea(['rows' => 3]) ?>

        </div>

        <div class="col-md-8 col-xs-12">
            
            <h4>II. Staff Information</h4>
            <br>
            <?= $form->field($model, 'staffs')->widget(Select2::classname(), [
                'data' => $staffs,
                'options' => ['placeholder' => 'Select staff','multiple' => true, 'class'=>'staff-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
            ]) ?>
            
            <br>
            <h4>III. Destinations</h4>
            <br>
            <?php DynamicFormWidget::begin([
                'widgetContainer' => 'destination_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                'widgetBody' => '.destinations', // required: css class selector
                'widgetItem' => '.destination', // required: css class
                'limit' => 10, // the maximum times, an element can be cloned (default 999)
                'min' => 1, // 0 or 1 (default 1)
                'insertButton' => '.add-destination', // css class
                'deleteButton' => '.remove-destination', // css class
                'model' => $destinationModels[0],
                'formId' => 'travel-order-form',
                'formFields' => [
                    'region',
                    'province',
                    'citymun',
                    'specificLocation',
                ],
            ]); ?>
            
            <table class="table table-bordered table-condensed table-responsive">
                <thead>
                    <tr>
                        <td align=center style="width: 5%;"><b>#</b></td>
                        <td align=center style="width: 21%;"><b>Region *</b></td>
                        <td align=center style="width: 21%;"><b>Province *</b></td>
                        <td align=center style="width: 21%;"><b>City/Municipality *</b></td>
                        <td align=center style="width: 21%;"><b>Specific Location *</b></td>
                        <td style="width: 10%;"><button type="button" class="pull-right add-destination btn btn-primary btn-xs">Add More Destination</button></td>
                    </tr>
                </thead>
                <tbody class="destinations">
                <?php foreach ($destinationModels as $idx => $destinationModel){ ?>
                    <?php
                        // necessary for update action.
                        if (!$destinationModel->isNewRecord) {
                            echo Html::activeHiddenInput($destinationModel, "[{$idx}]loc_id");
                        }
                    ?>
                    <tr class="destination">
                        <td class="destination-counter" align=center style="width: 5%;"><?= ($idx + 1) ?></td>
                        <td style="width: 21%;"><?= $form->field($destinationModel, "[{$idx}]region")->widget(Select2::classname(), [
                            'data' => $regions,
                            'options' => ['multiple' => false, 'placeholder' => 'Select region', 'class'=>'region-select'],
                            'pluginOptions' => [
                                'allowClear' =>  true,
                            ],
                            'pluginEvents'=>[
                                'select2:select'=>'
                                    function(){
                                        $.ajax({
                                            url: "'.Url::to(['/v1/travel-order/province-list']).'",
                                            data: {
                                                    id: this.value,
                                                }
                                        }).done(function(result) {
                                            $("#travelorderlocation-'.$idx.'-province").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select province", allowClear: true});
                                            $("#travelorderlocation-'.$idx.'-province").select2("val","");
                                            $("#travelorderlocation-'.$idx.'-citymun").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select citymun", allowClear: true});
                                            $("#travelorderlocation-'.$idx.'-citymun").select2("val","");
                                        });
                                    }'
                
                            ]
                            ])->label(false);
                        ?></td>
                        <td style="width: 21%;"><?= $form->field($destinationModel, "[{$idx}]province")->widget(Select2::classname(), [
                            'data' => Yii::$app->controller->action->id === 'update' ? $provinces[$idx] : $provinces,
                            'options' => ['multiple' => false, 'placeholder' => 'Select province', 'class'=>'province-select'],
                            'pluginOptions' => [
                                'allowClear' =>  true,
                            ],
                            'pluginEvents'=>[
                                'select2:select'=>'
                                    function(){
                                        $.ajax({
                                            url: "'.Url::to(['/v1/travel-order/citymun-list']).'",
                                            data: {
                                                    id: this.value,
                                                }
                                        }).done(function(result) {
                                            $("#travelorderlocation-'.$idx.'-citymun").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select city/municipality", allowClear: true});
                                            $("#travelorderlocation-'.$idx.'-citymun").select2("val","");
                                        });
                                    }'
                
                            ]
                            ])->label(false);
                        ?></td>
                        <td style="width: 21%;"><?= $form->field($destinationModel, "[{$idx}]citymun")->widget(Select2::classname(), [
                            'data' => Yii::$app->controller->action->id === 'update' ? $citymuns[$idx] : $citymuns,
                            'options' => ['multiple' => false, 'placeholder' => 'Select city/municipality', 'class'=>'citymun-select'],
                            'pluginOptions' => [
                                'allowClear' =>  true,
                            ],
                            ])->label(false);
                        ?></td>
                        <td style="width: 21%;"><?= $form->field($destinationModel, "[{$idx}]specificLocation")->textInput(['maxlength' => true])->label(false) ?></td>
                        <td style="width: 10%;"><button type="button" class="pull-right remove-destination btn btn-danger btn-xs"><i class="fa fa-minus"></i></button></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php DynamicFormWidget::end(); ?>

        </div>

    </div>  

    <br>
    <br>

    <div class="pull-right">
        <?= Html::a('Cancel', ['/v1/travel-order'], ['class' => 'btn btn-default']) ?>
        <?= Html::submitButton('Save Travel Order', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>
    <div class="clearfix"></div>

    <?php ActiveForm::end(); ?>

</div>
