<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use kartik\tabs\TabsX;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Ris */

$this->title = $model->statusName != 'No s' ? $model->ris_no.' ['.$model->statusName.']' : $model->ris_no;
$this->params['breadcrumbs'][] = ['label' => 'RIS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="ris-view">
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>
    <div class="row">
        <div class="col-md-5 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-edit"></i> Add Original Item Form</div>
                <div class="box-body">
                    <?= $this->render('_home',[
                        'model' => $model,
                        'appropriationItemModel' => $appropriationItemModel,
                        'activities' => $activities,
                        'subActivities' => $subActivities,
                        'items' => $items,
                        'months' => $months
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-7 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i>RIS Original Items</div>
                <div class="box-body">
                    <div id="original-items"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
  $script = '
    function loadOriginalItems()
    {
        $.ajax({
            url: "'.Url::to(['/v1/ris/original']).'",
            data: {
                id: '.$model->id.'
            },
            beforeSend: function(){
                $("#original-items").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
            },
            success: function (data) {
                console.log(this.data);
                $("#original-items").empty();
                $("#original-items").hide();
                $("#original-items").fadeIn("slow");
                $("#original-items").html(data);
            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    $(document).ready(function() {
        loadOriginalItems();
    });
  ';
  $this->registerJs($script, View::POS_END);
?>