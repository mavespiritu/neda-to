<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
/* @var $model common\modules\v1\models\Ppmp */
/* @var $form yii\widgets\ActiveForm */
$itemsUrl = \yii\helpers\Url::to(['/v1/ris/item-list']);
$item_id = $itemModel->isNewRecord ? 0 : $itemModel->item_id;
?>

<div class="ppmp-item">
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
        'id' => 'supplemental-items-form',
    ]); ?>

    <?= Html::hiddenInput('cost_per_unit', $itemModel->isNewRecord ? '' : $itemModel->item->cost_per_unit, ['id' => 'cost_per_unit']) ?>
    
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <?php 
                $subActivitiesUrl = \yii\helpers\Url::to(['/v1/ris/sub-activity-list']);
                echo $form->field($itemModel, 'activity_id')->widget(Select2::classname(), [
                'data' => $activities,
                'options' => ['placeholder' => 'Select Activity','multiple' => false, 'class'=>'activity-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                'pluginEvents'=>[
                    'select2:select'=>'
                        function(){
                            $.ajax({
                                url: "'.$subActivitiesUrl.'",
                                data: {
                                        id: this.value
                                    }
                                
                            }).done(function(result) {
                                $(".sub-activity-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select PPA", allowClear: true});
                                $(".sub-activity-select").select2("val","");
                            });
                        }'

                ]
                ]);
            ?>
        </div>
        <div class="col-md-12 col-xs-12">
            <?= $form->field($itemModel, 'sub_activity_id')->widget(Select2::classname(), [
                    'data' => $subActivities,
                    'options' => ['placeholder' => 'Select PPA', 'multiple' => false, 'class' => 'sub-activity-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    
                ]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xs-12">
            <?= $form->field($itemModel, 'obj_id')->widget(Select2::classname(), [
                    'data' => $objects,
                    'options' => ['placeholder' => 'Select Object', 'multiple' => false, 'class' => 'obj-select'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    'pluginEvents' => [
                        'select2:select'=>'
                            function(){
                                $.ajax({
                                    url: "'.$itemsUrl.'",
                                    data: {
                                            id: '.$model->id.',
                                            obj_id: this.value,
                                            type: "Supplemental"
                                        }
                                    
                                }).done(function(result) {
                                    $(".item-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select Item", allowClear: true});
                                    $(".item-select").select2("val","");
                                });
                            }'

                    ]
                ])->label('Object');
            ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <?= $form->field($itemModel, 'item_id')->widget(Select2::classname(), [
                    'data' => $items,
                    'options' => ['placeholder' => 'Select Item', 'multiple' => false, 'class' => 'item-select', 'id' => 'ppmpitem-item_id-'.$itemModel->sub_activity_id],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    'pluginEvents' => [
                        'select2:select'=>'
                            function(){
                                updateItemDetails(this.value);
                                getTotal();
                            }'
                    ]
                ]);
            ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <div class="form-group">
                <label class="control-label">Unit of Measure</label>
                <?= Html::textInput('unit_of_measure', $itemModel->isNewRecord ? '' : $itemModel->item->unit_of_measure, ['disabled' => 'disabled', 'class' => 'form-control', 'id' => 'ppmp-item-unit_of_measure']); ?>
            </div>
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="form-group">
            <?= $form->field($itemModel, 'cost')->widget(MaskedInput::classname(), [
                'options' => [
                    'autocomplete' => 'off',
                    'onchange' => 'getTotal()',
                    'onkeyup' => 'getTotal()',
                ],
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'removeMaskOnSubmit' => true,
                    'groupSeparator' => ',',
                    'autoGroup' => true
                ],
            ])->label('Cost Per Unit') ?>
            </div>
        </div>
    </div>
    
    <label for="quantity" class="control-label">Quantity</label>
    
    <?php if($months){ ?>
        <div class="row">
            <?php foreach($months as $idx => $month){ ?>
                <?php if($idx <= 5){ ?>
                <div class="col-md-2 col-xs-12">
                    <?= $form->field($itemBreakdowns[$month->id], "[$month->id]month_id")->hiddenInput(['value' => $month->id])->label(false) ?>
                    <?= $form->field($itemBreakdowns[$month->id], "[$month->id]quantity")->textInput(['type' => 'number', 'maxlength' => true, 'min' => 0, 'onchange' => 'getTotal()', 'onkeyup' => 'getTotal()', 'value' => $itemBreakdowns[$month->id]->quantity > 0 ? $itemBreakdowns[$month->id]->quantity : 0])->label($month->abbreviation) ?>
                </div>
                <?php } ?>
            <?php } ?>
        </div>
        <div class="row">
            <?php foreach($months as $idx => $month){ ?>
                <?php if($idx > 5){ ?>
                <div class="col-md-2 col-xs-12">
                    <?= $form->field($itemBreakdowns[$month->id], "[$month->id]month_id")->hiddenInput(['value' => $month->id])->label(false) ?>
                    <?= $form->field($itemBreakdowns[$month->id], "[$month->id]quantity")->textInput(['type' => 'number', 'maxlength' => true, 'min' => 0, 'onchange' => 'getTotal()', 'onkeyup' => 'getTotal()', 'value' => $itemBreakdowns[$month->id]->quantity > 0 ? $itemBreakdowns[$month->id]->quantity : 0])->label($month->abbreviation) ?>
                </div>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
    

    <div class="row">
        <div class="col-md-12 col-xs-12">
            <?= $form->field($itemModel, 'remarks')->textArea(['rows' => 3]) ?>
        </div>
    </div>
    
    <span class="pull-right">Total</span><br>
    <p class="panel-title pull-right" style="font-size: 35px !important;" id="total-per-item"></p>
    <p class="clearfix"></p>
    
    <div class="form-group pull-right">
        <?= ($model->status->status == 'Draft' || $model->status->status == 'For Revision') ? Html::submitButton('Add to RIS', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) : '' ?>
    </div>
    <div class="clearfix"></div>
    <?php ActiveForm::end(); ?>
    </div>
<?php
  $script = '
    function getTotal()
    {
        $("#cost_per_unit").val($("#ppmpitem-cost").val());
        var cost_per_unit = $("#cost_per_unit").val().split(",").join("");
            cost_per_unit = parseFloat(cost_per_unit);
        var jan = parseInt($("#itembreakdown-1-quantity").val());
        var feb = parseInt($("#itembreakdown-2-quantity").val());
        var mar = parseInt($("#itembreakdown-3-quantity").val());
        var apr = parseInt($("#itembreakdown-4-quantity").val());
        var may = parseInt($("#itembreakdown-5-quantity").val());
        var jun = parseInt($("#itembreakdown-6-quantity").val());
        var jul = parseInt($("#itembreakdown-7-quantity").val());
        var aug = parseInt($("#itembreakdown-8-quantity").val());
        var sep = parseInt($("#itembreakdown-9-quantity").val());
        var oct = parseInt($("#itembreakdown-10-quantity").val());
        var nov = parseInt($("#itembreakdown-11-quantity").val());
        var dec = parseInt($("#itembreakdown-12-quantity").val());

        var total = jan + feb + mar + apr + may + jun + jul + aug + sep + oct + nov + dec;

        grandTotal = total * cost_per_unit;

        $("#total-per-item").empty();
        $("#total-per-item").html(number_format(grandTotal, 2, ".", ","));       
    }

    function updateItemDetails(id)
    {
        $.ajax({
            url: "'.Url::to(['/v1/ppmp/unit-of-measure']).'",
            data: {
                    id: id,
                  }
        }).done(function(result) {
            $("#ppmp-item-unit_of_measure").empty();
            $("#ppmp-item-unit_of_measure").fadeIn("slow");
            $("#ppmp-item-unit_of_measure").val(result);

        });

        $.ajax({
            url: "'.Url::to(['/v1/ppmp/cost']).'",
            data: {
                    id: id,
                  }
        }).done(function(result) {
            $("#ppmpitem-cost").val(result);
            $("#cost_per_unit").val(result);
        });

        getTotal();
    }

    function number_format (number, decimals, dec_point, thousands_sep) {
        number = (number + "").replace(/[^0-9+\-Ee.]/g, "");
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === "undefined") ? "," : thousands_sep,
            dec = (typeof dec_point === "undefined") ? "." : dec_point,
            s = "",
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return "" + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : "" + Math.round(n)).split(".");
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || "").length < prec) {
            s[1] = s[1] || "";
            s[1] += new Array(prec - s[1].length + 1).join("0");
        }
        return s.join(dec);
    }

    $(document).ready(function() {
        getTotal();
    });

  ';
  $this->registerJs($script, View::POS_END);
?>