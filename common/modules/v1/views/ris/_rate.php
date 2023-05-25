<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\Modal;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Ris */

$this->title = $model->statusName != 'No status' ? $model->ris_no.' - '.$model->purpose.' ['.$model->statusName.']' : $model->ris_no.' - '.$model->purpose;
$this->title = strlen($this->title) > 70 ? substr($this->title, 0, 70).'...' : $this->title;
$this->params['breadcrumbs'][] = ['label' => 'RIS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $i = 1; ?>
<div class="ris-view">
    <?= $this->render('_menu', [
        'model' => $model,
    ]) ?>
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-edit"></i> Rate Items
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3 col-xs-12">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Issued By</th>
                                        <th>Date Issued</th>
                                        <td align=center><b>Avg Rating</b></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if($issuances){ ?>
                                    <?php foreach($issuances as $issuance){ ?>
                                        <tr>
                                            <td><?= $issuance->issuerName ?></td>
                                            <td><?= $issuance->issuance_date ?></td>
                                            <td align=center><?= number_format($issuance->averageRating, 1) ?></td>
                                            <td align=center>
                                                <?= Html::button('Rate Items', ['onclick' => 'rateIssuance('.$issuance->id.')', 'class' => 'btn btn-xs btn-primary']) ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php }else{ ?>
                                    <tr>
                                        <td colspan=3 align=center>No issuances</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-9 col-xs-12">
                            <div id="rate-item-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    $script = '
        function rateIssuance(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ris/rate-issuance']).'?id="+ id,
                beforeSend: function(){
                    $("#rate-item-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    $("#rate-item-content").empty();
                    $("#rate-item-content").hide();
                    $("#rate-item-content").fadeIn("slow");
                    $("#rate-item-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    ';

    $this->registerJs($script, View::POS_END);
?>