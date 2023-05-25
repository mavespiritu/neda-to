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
                <div class="box-header panel-title"><i class="fa fa-edit"></i> Issue Items
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3 col-xs-12">
                            <?= Html::button('Create Issuance', ['value' => Url::to(['/v1/ris/create-issuance', 'id' => $model->id]), 'class' => 'btn btn-success btn-sm', 'id' => 'issue-button']) ?>
                            <br>
                            <br>
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Issued By</th>
                                        <th>Date Issued</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if($issuances){ ?>
                                    <?php foreach($issuances as $issuance){ ?>
                                        <tr>
                                            <td><?= $issuance->issuerName ?></td>
                                            <td><?= $issuance->issuance_date ?></td>
                                            <td>
                                                <?= Html::button('<i class="fa fa-pencil"></i>', ['value' => Url::to(['/v1/ris/update-issuance', 'id' => $issuance->id]), 'class' => 'btn btn-info btn-xs update-issue-button']) ?>
                                                <?= Html::a('<i class="fa fa-trash"></i>', ['delete-issuance', 'id' => $issuance->id], [
                                                    'class' => 'btn btn-xs btn-danger',
                                                    'data' => [
                                                        'confirm' => 'Are you sure you want to delete this record?',
                                                        'method' => 'post',
                                                    ],
                                                ]) ?>
                                            </td>
                                            <td align=center>
                                                <?= Html::button('Issue Items', ['onclick' => 'viewIssuance('.$issuance->id.')', 'class' => 'btn btn-xs btn-primary']) ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php }else{ ?>
                                    <tr>
                                        <td colspan=4 align=center>No issuances</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-9 col-xs-12">
                            <div id="issue-item-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
  Modal::begin([
    'id' => 'issue-item-modal',
    'size' => "modal-sm",
    'header' => '<div id="issue-item-modal-header"><h4>Create Issuance</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="issue-item-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-issue-modal',
    'size' => 'modal-sm',
    'header' => '<div id="update-issue-modal-header"><h4>Edit Issuance</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-issue-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function viewIssuance(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ris/view-issuance']).'?id="+ id,
                beforeSend: function(){
                    $("#issue-item-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    $("#issue-item-content").empty();
                    $("#issue-item-content").hide();
                    $("#issue-item-content").fadeIn("slow");
                    $("#issue-item-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            $("#issue-button").click(function(){
                $("#issue-item-modal").modal("show").find("#issue-item-modal-content").load($(this).attr("value"));
            });
            $(".update-issue-button").click(function(){
                $("#update-issue-modal").modal("show").find("#update-issue-modal-content").load($(this).attr("value"));
            });
        });
    ';

    $this->registerJs($script, View::POS_END);
?>