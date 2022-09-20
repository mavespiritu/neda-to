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
        <div class="col-md-2 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> Main Navigation</div>
                <div class="box-body">
                <?= $this->render('_pr-menu', [
                    'model' => $model
                ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xs-12">
            <div id="pr-main">
            </div>
        </div>
        <div class="col-md-2 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-edit"></i> To Dos</div>
                <div class="box-body">
                    <ul class="todos" style="font-size: 13px; line-height: 2rem;" type="none" >
                        <li><a href="javascript:void(0);" onclick="items(<?= $model->id?>);">Add Items</a></li>
                        <li><a href="javascript:void(0);" onclick="dbmItems(<?= $model->id?>);">Group Items</a></li>
                        <?php if(!empty($model->aprItems)){ ?>
                        <li><a href="javascript:void(0);" onclick="dbmPricing(<?= $model->id?>);">Set DBM-PS Pricing</a></li>
                        <?php } ?>
                        <?php if(!empty($model->rfqItems)){ ?>
                        <li><a href="javascript:void(0);" onclick="quotations(<?= $model->id?>);">Generate RFQ</a></li>
                        <li><a href="javascript:void(0);" onclick="retrieveQuotations(<?= $model->id?>);">Retrieve RFQs</a></li>
                        <li><a href="javascript:void(0);" onclick="retrieveQuotations(<?= $model->id?>);">Set Winners</a></li>
                        <?php } ?>
                        <?php if($model->type == 'Supply'){ ?>
                            <li>Set Purchase Order</li>
                        <?php }else{ ?>
                            <li>Set Contract</li>
                        <?php } ?>
                        <li>Inspect Items</li>
                        <li>Issue Items</li>
                    </ul>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-file-o"></i> Reports</div>
                <div class="box-body">
                    <ul class="reports" style="font-size: 13px; line-height: 2rem;" type="none">
                        <li><?= Html::button('<i class="fa fa-print"></i> Purchase Request (PR)', ['value' => Url::to(['/v1/pr/pr', 'id' => $model->id]), 'class' => 'button-link', 'id' => 'pr-button']) ?></li>
                        <li><?= Html::button('<i class="fa fa-print"></i> Agency Purchase Request (APR)', ['value' => Url::to(['/v1/pr/apr', 'id' => $model->id]), 'class' => 'button-link', 'id' => 'apr-button']) ?></li>
                        <li><?= Html::button('<i class="fa fa-print"></i> Request For Quotation (RFQ)', ['value' => Url::to(['/v1/pr/rfq', 'id' => $model->id]), 'class' => 'button-link', 'id' => 'rfq-button']) ?></li>
                        <li><?= Html::button('<i class="fa fa-print"></i> Abstract of Quotation (AOQ)', ['value' => Url::to(['/v1/pr/aoq', 'id' => $model->id]), 'class' => 'button-link', 'id' => 'aoq-button']) ?></li>
                        <?php if($model->type == 'Supply'){ ?>
                        <li><i class="fa fa-print"></i> Purchase Order (PO)</li>
                        <?php }else{ ?>
                        <li><i class="fa fa-print"></i> Contract</li>
                        <?php } ?>
                        <li><i class="fa fa-print"></i> Notice of Award (NOA)</li>
                        <li><i class="fa fa-print"></i> Notice to Proceed (NTP)</li>
                        <li><i class="fa fa-print"></i> Obligation Request Status (ORS)</li>
                        <li><i class="fa fa-print"></i> Disbursement Voucher (DV)</li>
                        <li><i class="fa fa-print"></i> Inspection and Acceptance Report (IAR)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    $script = '
        $(document).ready(function(){
            home('.$model->id.');
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