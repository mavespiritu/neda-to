<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use frontend\assets\AppAsset;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Iar */

$this->title = 'IAR No. '.$model->iar_no;
$this->params['breadcrumbs'][] = ['label' => 'IARs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $form = ActiveForm::begin([
    'id' => 'iar-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>
<div class="iar-view">
    <div class="pull-left">
        <?= Html::a('<i class="fa fa-angle-double-left"></i> Back to IAR List', ['/'.Yii::$app->session->get('IAR_ReturnURL')], ['class' => 'btn btn-app']) ?>
    </div>
    <div class="pull-right">
        <?= Html::a('<i class="fa fa-print"></i> Print IAR', null, ['class' => 'btn btn-app', 'onclick' => 'printIar('.$model->id.')']) ?>
        <?= Html::button('<i class="fa fa-edit"></i> Edit IAR', ['value' => Url::to(['/v1/iar/update', 'id' => $model->id]), 'class' => 'btn btn-app', 'id' => 'update-button']) ?>
        <?= Html::a('<i class="fa fa-trash"></i> Delete IAR', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-app',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </div>
    <div class="clearfix"></div>
    <div class="box box-primary">
        <div class="box-header panel-title"><i class="fa fa-list"></i> PR No. <?= $model->po->pr->pr_no ?> : IAR Information
        </div>
        <div class="box-body">
            <h4 class="text-center">Inspection and Acceptance</h4>
            <br>
            <table style="width: 100%;">
                <tr>
                    <td><b>Entity Name:  </b><u><?= $entity->value ?></u></td>
                    <td><b>Fund Cluster:  </b><u><?= $pr->fundCluster->title ?></u></td>
                </tr>
            </table>
            <br>
            <table class="table table-bordered table-hover table-condensed table-responsive">
                <tr>
                    <td style="width: 50%; line-height: 30px;">
                        <p>
                            <b>Supplier: </b><u><?= $po->supplier->business_name ?></u><br>
                            <b>Purchase Order/Contract No.: </b><u><?= $po->po_no ?></u>&nbsp;&nbsp;&nbsp;&nbsp;<b>Date: </b><u><?= $po->po_date ?></u><br>
                            <b>Requisitioning Office/Dept.: </b><u><?= $pr->officeName ?></u><br>
                            <b>Responsibility Center Code: </b><u><?= implode(', ', $rccs) ?></u><br>
                        </p>
                    </td>
                    <td style="width: 50%; line-height: 30px;">
                        <p>
                            <b>IAR No.: </b><u><?= $model->iar_no ?></u><br>
                            <b>Date: </b><u><?= $model->iar_date ?></u><br>
                            <b>Invoice No.: </b><u><?= $model->invoice_no ?></u><br>
                            <b>Date: </b><u><?= $model->invoice_date ?></u><br>
                        </p>
                    </td>
                </tr>
            </table>
            <br>
            <table class="table table-bordered table-striped table-hover table-condensed table-responsive">
                <thead>
                    <tr>
                        <td align=center><b>Stock/Property No.</b></td>
                        <td align=center><b>Description</b></td>
                        <td align=center><b>Unit</b></td>
                        <td align=center><b>Qty</b></td>
                        <td align=center><b>Balance</b></td>
                        <td align=center><b>Delivered</b></td>
                        <td align=center><b>Delivery Time</b></td>
                        <td align=center><b>Courtesy of <br> Delivery Staff</b></td>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($items)){ ?>
                    <?php foreach($items as $item){ ?>
                        <?php $id = $item['id']; ?>
                        <tr>
                            <td align=center><?= $item['id'] ?></td>
                            <td><?= $item['item'] ?></td>
                            <td><?= $item['unit'] ?></td>
                            <td align=center><?= number_format($item['total'], 0) ?></td>
                            <td align=center><?= number_format($item['balance'], 0) ?></td>
                            <td><?= $form->field($itemModels[$item['id']], "[$id]balance")->textInput(['type' => 'number', 'max' => $item['balance'], 'min' => 0])->label(false) ?></td>
                            <td><?= $form->field($itemModels[$item['id']], "[$id]delivery_time")->dropdownList(['5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1'])->label(false) ?></td>
                            <td><?= $form->field($itemModels[$item['id']], "[$id]courtesy")->dropdownList(['5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1'])->label(false) ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td align=center><b>xxxxxx NOTHING FOLLOWS xxxxxx</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan=4 align=center><b><i>INSPECTION</i></b></td>
                        <td colspan=5 align=center><b><i>ACCEPTANCE</i></b></td>
                    </tr>
                    <tr>
                        <td colspan=4>
                            <b>Date Inspected:  </b><u><?= date("F j, Y", strtotime($model->date_inspected)) ?></u>
                            <br>
                            <br>
                            &#9745; Inspected, verified and found in order as to quantity and specifications.
                            <br>
                            <br>
                            <br>
                            <p class="text-center">
                                <b><u><?= strtoupper($model->inspectorName) ?></u></b>
                                <br>
                                Inspection Officer/Inspection Committee
                            </p>
                        </td>
                        <td colspan=5>
                            <b>Date Received:  </b><u><?= date("F j, Y", strtotime($model->date_received)) ?></u>
                            <br>
                            <?= $model->status == 'Complete' ? '&#9745;' : '&#9744;' ?> Complete 
                            <br>
                            <?= $model->status == 'Partial' ? '&#9745;' : '&#9744;' ?> Partial (pls. specify quantity)
                            <br>
                            <br>
                            <br>
                            <p class="text-center">
                                <b><u><?= strtoupper($model->receiverName) ?></u></b>
                                <br>
                                Supply and/or Property Custodian
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <div class="pull-right">
            <?= Html::submitButton('<i class="fa fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
<?php Modal::begin([
    'id' => 'update-modal',
    'size' => "modal-md",
    'header' => '<div id="update-modal-header"><h4>Update IAR No. '.$model->iar_no.'</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(document).ready(function(){
            $("#update-button").click(function(){
              $("#update-modal").modal("show").find("#update-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>
<?php
    $script = '
        function printIar(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-iar']).'?id=" + id, 
                "Print",
                "left=200", 
                "top=200", 
                "width=650", 
                "height=500", 
                "toolbar=0", 
                "resizable=0"
                );
                printWindow.addEventListener("load", function() {
                    printWindow.print();
                    setTimeout(function() {
                    printWindow.close();
                }, 1);
                }, true);
        }
    ';

    $this->registerJs($script, View::POS_END);
?>