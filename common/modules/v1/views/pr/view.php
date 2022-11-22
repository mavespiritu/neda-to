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
          home('.$model->id.');
          menu('.$model->id.');
        });    
    ';

    $this->registerJs($script, View::POS_END);
?>