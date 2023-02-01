<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
/* @var $model common\modules\v1\models\Ppmp */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ppmp">

    <?php $form = ActiveForm::begin([
    	//'options' => ['class' => 'disable-submit-buttons'],
        'id' => 'ris-activity-form',
    ]); ?>

        <div class="row">
            <div class="col-md-12 col-xs-12">
                <?php 
                    $subActivitiesUrl = \yii\helpers\Url::to(['/v1/ris/sub-activity-list']);
                    $itemsUrl = \yii\helpers\Url::to(['/v1/ris/original-item-list']);
                    echo $form->field($appropriationItemModel, 'activity_id')->widget(Select2::classname(), [
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
                                $.ajax({
                                    url: "'.$itemsUrl.'",
                                    data: {
                                            id: '.$model->id.',
                                            activity_id: this.value,
                                            sub_activity_id: $("#appropriationitem-sub_activity_id").val()
                                        }
                                    
                                }).done(function(result) {
                                    $(".item-select").html("").select2({ data:result, theme:"krajee", width:"100%", placeholder:"Select Items", allowClear:true, multiple:true});
                                    $(".item-select").select2("val","");
                                });
                            }'
                    ]
                    ]);
                ?>
            </div>
            <div class="col-md-12 col-xs-12">
                <?php 
                    echo $form->field($appropriationItemModel, 'sub_activity_id')->widget(Select2::classname(), [
                    'data' => $subActivities,
                    'options' => ['placeholder' => 'Select PPA','multiple' => false, 'class'=>'sub-activity-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    'pluginEvents'=>[
                        'select2:select'=>'
                            function(){
                                $.ajax({
                                    url: "'.$itemsUrl.'",
                                    data: {
                                            id: '.$model->id.',
                                            activity_id: $("#appropriationitem-activity_id").val(),
                                            sub_activity_id: this.value
                                        }
                                    
                                }).done(function(result) {
                                    $(".item-select").html("").select2({ data:result, theme:"krajee", width:"100%", placeholder:"Select Items", allowClear:true, multiple:true});
                                    $(".item-select").select2("val","");
                                });
                            }'

                    ]
                    ]);
                ?>
            </div>
            <div class="col-md-12 col-xs-12">
                <?= $form->field($appropriationItemModel, 'item_id')->widget(Select2::classname(), [
                        'data' => $items,
                        'options' => ['placeholder' => 'Select Items','multiple' => true, 'class'=>'item-select'],
                        'pluginOptions' => [
                            'allowClear' =>  true,
                        ],
                    ]);
                ?>
            </div>
            <div class="col-md-12 col-xs-12">
                <?= $form->field($appropriationItemModel, 'month_id')->widget(Select2::classname(), [
                        'data' => $months,
                        'options' => ['placeholder' => 'Select Month','multiple' => true, 'class'=>'month-select'],
                        'pluginOptions' => [
                            'allowClear' =>  true,
                        ],
                    ])->label('Months (For bulk orders)');
                ?>
            </div>
            <div class="col-md-12 col-xs-12">
                <div class="form-group pull-right">
                    <?= Html::submitButton('<i class="fa fa-refresh"></i> Load Items', ['class' => 'btn btn-success btn-block']) ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
  $script = '
    $("#ris-activity-form").on("beforeSubmit", function(e) {
        var form = $(this);
        e.preventDefault();
        $.ajax({
            url: "'.Url::to(['/v1/ris/load-items']).'",
            data: {
                id: '.$model->id.',
                activity_id: $("#appropriationitem-activity_id").val(),
                sub_activity_id: $("#appropriationitem-sub_activity_id").val(),
                item_id: JSON.stringify($("#appropriationitem-item_id").val()),
                month_id: JSON.stringify($("#appropriationitem-month_id").val()),
            },
            beforeSend: function(){
                $("#ris-item-list").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
            },
            success: function (data) {
                console.log(this.data);
                $("#ris-item-list").empty();
                $("#ris-item-list").hide();
                $("#ris-item-list").fadeIn("slow");
                $("#ris-item-list").html(data);
            },
            error: function (err) {
                console.log(err);
            }
        });

        return false;
    });
    $(document).ready(function() {
        
    });
  ';
  $this->registerJs($script, View::POS_END);
?>