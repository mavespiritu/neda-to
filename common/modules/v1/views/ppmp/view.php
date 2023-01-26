<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
use yii\bootstrap\Modal;
/* @var $model common\modules\v1\models\Ppmp */

$this->title = $model->status ? $model->title.' ['.$model->status->status.']' : $model->title;
$this->params['breadcrumbs'][] = ['label' => 'PPMPs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="ppmp-view">
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i>Manage Items</div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <div id="alert-container"></div>
                            <?= $this->render('_load-items', [
                                'model' => $model,
                                'appropriationItemModel' => $appropriationItemModel,
                                'activities' => $activities,
                                'fundSources' => $fundSources,
                            ]) ?>
                            <div id="item-details"></div>
                        </div>
                    </div>
                    <hr style="opacity: 0.3" />
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                            <div class="row">
                                <div class="col-md-2 col-xs-12">
                                    <div id="item-activity"></div>
                                </div>
                                <div class="col-md-10 col-xs-12">
                                    <div id="items"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    $script = '
        $(document).ready(function(){
            $("#reference-button").click(function(){
              $("#items").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>
<?php
    $script = '
        function checkPrices(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/check-price']).'",
                data: {
                    id: id,
                },
                success: function (data) {
                    $("#alert-container").empty();
                    $("#alert-container").hide();
                    $("#alert-container").fadeIn("slow");
                    $("#alert-container").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function loadItemsInSubActivity(id, sub_activity_id, activity_id, fund_source_id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/load-items-in-sub-activity']).'",
                data: {
                    id: id,
                    sub_activity_id: sub_activity_id,
                    activity_id: activity_id,
                    fund_source_id: fund_source_id,
                },
                beforeSend: function(){
                    $("#item-list-" + sub_activity_id).html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    $("#item-list-" + sub_activity_id).empty();
                    $("#item-list-" + sub_activity_id).hide();
                    $("#item-list-" + sub_activity_id).fadeIn("slow");
                    $("#item-list-" + sub_activity_id).html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function ignoreAlert(id, con)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/ignore-alert']).'",
                data: {
                    id: id,
                    con: con,
                },
                success: function (data) {
                    $("#alert-container").empty();
                    $("#alert-container").hide();
                    $("#alert-container").fadeIn("slow");
                    $("#alert-container").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function loadPpmpTotal(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/load-ppmp-total']).'",
                data: {
                    id: id,
                },
                success: function (data) {
                    $("#ppmp-total").empty();
                    $("#ppmp-total").hide();
                    $("#ppmp-total").fadeIn("slow");
                    $("#ppmp-total").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function loadOriginalTotal(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/load-original-total']).'",
                data: {
                    id: id,
                },
                success: function (data) {
                    $("#original-total").empty();
                    $("#original-total").hide();
                    $("#original-total").fadeIn("slow");
                    $("#original-total").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function loadSupplementalTotal(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/load-supplemental-total']).'",
                data: {
                    id: id,
                },
                success: function (data) {
                    $("#supplemental-total").empty();
                    $("#supplemental-total").hide();
                    $("#supplemental-total").fadeIn("slow");
                    $("#supplemental-total").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function loadItemSummary(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/load-item-summary']).'",
                data: {
                    id: id,
                },
                success: function (data) {
                    $("#item-summary").empty();
                    $("#item-summary").hide();
                    $("#item-summary").fadeIn("slow");
                    $("#item-summary").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function loadItemActivity(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/item-activity']).'",
                data: {
                    id: id,
                },
                success: function (data) {
                    $("#item-activity").empty();
                    $("#item-activity").hide();
                    $("#item-activity").fadeIn("slow");
                    $("#item-activity").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function loadItems(id, activity_id, fund_source_id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/load-items']).'",
                data: {
                    id: id,
                    activity_id: activity_id,
                    fund_source_id: fund_source_id,
                },
                beforeSend: function(){
                    $("#items").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    $("#items").empty();
                    $("#items").hide();
                    $("#items").fadeIn("slow");
                    $("#items").html(data);
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            loadItemSummary('.$model->id.');
            loadItemActivity('.$model->id.');
            //checkPrices('.$model->id.');
        });
    ';

    $this->registerJs($script, View::POS_END);
?>