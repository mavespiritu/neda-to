<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\web\View;
use yii\bootstrap\Modal;
/* @var $model common\modules\v1\models\Pr */

$this->title = $model->status ? $model->pr_no.' ['.$model->status->status.']' : $model->pr_no;
$this->params['breadcrumbs'][] = ['label' => 'PRs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="pr-view">
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> PR Information</div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12 col-xs-12">
                          <div id="menu"></div>
                        </div>
                        <div class="col-md-12 col-xs-12">
                          <div id="pr-main"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    $script = '
        function highlightButton(step)
        {
          var button = $("#"+step);
          var buttons = $(":a.main-menu");
          for(let index = 0 ; index < buttons.length ; ++index){
            
          }
        }
        $(document).ready(function(){
            home('.$model->id.');
            menu('.$model->id.');
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>
<style>
    .button-link {
        background: none !important;
        border: none;
        padding: 0 !important;
        color: #6192CD;
        text-decoration: none;
        cursor: pointer;
    }
</style>
<?php
  Modal::begin([
    'id' => 'pr-modal',
    'size' => "modal-xl",
    'header' => '<div id="pr-modal-header"><h4>Purchase Request (PR)</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="pr-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'apr-modal',
    'size' => "modal-xl",
    'header' => '<div id="apr-modal-header"><h4>Agency Purchase Request (APR)</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="apr-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'rfq-modal',
    'size' => "modal-xl",
    'header' => '<div id="rfq-modal-header"><h4>Request For Quotation (RFQ)</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="rfq-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'aoq-modal',
    'size' => "modal-xl",
    'header' => '<div id="aoq-modal-header"><h4>Abstract of Quotation (AOQ)</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="aoq-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function home(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/home']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function menu(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/menu']).'?id=" + id,
                beforeSend: function(){
                    $("#menu").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#menu").empty();
                    $("#menu").hide();
                    $("#menu").fadeIn("slow");
                    $("#menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            $("#pr-button").click(function(){
                $("#pr-modal").modal("show").find("#pr-modal-content").load($(this).attr("value"));
              });
            $("#apr-button").click(function(){
                $("#apr-modal").modal("show").find("#apr-modal-content").load($(this).attr("value"));
              });
            $("#rfq-button").click(function(){
                $("#rfq-modal").modal("show").find("#rfq-modal-content").load($(this).attr("value"));
              });
            $("#aoq-button").click(function(){
                $("#aoq-modal").modal("show").find("#aoq-modal-content").load($(this).attr("value"));
              });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>