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
                <div class="box-header panel-title"><i class="fa fa-list"></i> RIS Information
                    <span class="pull-right">
                        <?= ButtonDropdown::widget([
                            'label' => '<i class="fa fa-download"></i> Export',
                            'encodeLabel' => false,
                            'options' => ['class' => 'btn btn-success'],
                            'dropdown' => [
                                'items' => [
                                    ['label' => 'Excel', 'url' => Url::to(['/v1/ris/download', 'type' => 'excel', 'id' => $model->id])],
                                    ['label' => 'PDF', 'url' => Url::to(['/v1/ris/download', 'type' => 'pdf', 'id' => $model->id])],
                                ],
                            ],
                            ]) ?>
                        <?= Html::button('<i class="fa fa-print"></i> Print', ['class' => 'btn btn-danger', 'onclick' => 'printRis()']) ?>
                    </span>
                </div>
                <div class="box-body">
                    <?php if($model->statusName == 'For Revision'){ ?> 
                        <div class="callout callout-info">
                            <h4 style="color: white;">RIS must be revised</h4>
                            <p>Remarks: <?= $model->status->remarks ?></p>
                        </div> 
                    <?php } ?>
                    <span class="clearfix"></span>
                    <h5 class="text-center"><b>REQUEST AND ISSUANCE SLIP</b></h5>
                    <p><b>Entity Name: <u><?= $entityName ?></u></b></p>
                    <p><b>Fund Cluster: <u><?= $fundClusterName ?></u></b></p>
                    <?php $total = 0; ?>
                    <table class="table table-bordered table-condensed">
                        <tr>
                            <td colspan=2>Division:<br>Office: </td>
                            <td colspan=5><u><?= $model->officeName ?></u><br><u><?= $model->officeName ?></u></td>
                            <td colspan=5>RIS No. <u><?= $model->ris_no ?></u></td>
                        </tr>
                        <tr>
                            <td colspan=7 align=center><b>Requisition</b></td>
                            <td rowspan=2><b>Stock Available?</b></td>
                            <td colspan=4 align=center><b>Issue</b></td>
                        </tr>
                        <tr>
                            <td align=center>#</td>
                            <td align=center>Stock No.</td>
                            <td align=center>Unit</td>
                            <td align=center>Description</td>
                            <td align=center>Quantity</td>
                            <td align=center>Unit Cost</td>
                            <td align=center>ABC</td>
                            <td align=center>Quantity</td>
                            <td align=center>Date Issue</td>
                            <td align=center>Remarks</td>
                            <td align=center>Fund Source</td>
                        </tr>
                        <?php if(!empty($risItems)){ ?>
                            <?php foreach($risItems as $activity => $subActivityitems){ ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <th colspan=2><?= $activity ?></th>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                                <?php if(!empty($subActivityitems)){ ?>
                                    <?php foreach($subActivityitems as $subActivity => $items){ ?>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <th><?= $subActivity ?> - <?= $model->fundSource->code ?> Funded</th>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                        <?php if(!empty($items)){ ?>
                                            <?php foreach($items as $item){ ?>
                                                <?= $this->render('_ris-item', [
                                                    'i' => $i,
                                                    'model' => $model,
                                                    'item' => $item,
                                                    'specifications' => $specifications
                                                ]) ?>
                                                <?php $total += ($item['total'] * $item['cost']); ?>
                                                <?php $i++; ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                        <tr>
                            <td colspan=6 align=right><b>Total</b></td>
                            <td align=right><b><?= number_format($total, 2) ?></b></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                    <br>
                    <table style="width: 50%; margin-left: 50%">
                        <tr>
                            <td style="width: 20%">Date Required:</td>
                            <td style="border-bottom: 1px solid #F4F4F4;"><?= $model->date_required ?></td>
                            <td style="width: 20%">&nbsp;</td>
                            <td style="width: 20%">&nbsp;</td>
                            <td style="width: 20%">&nbsp;</td>
                        </tr>
                    </table>
                    <br>
                    <table style="width: 80%;">
                        <tr>
                            <td style="width: 10%;">Purpose:</td>
                            <td style="width: 80%; border-bottom: 1px solid #F4F4F4;"><?= $model->purpose ?></td>
                        </tr>
                        <tr>
                            <td style="width: 10%; border: none">&nbsp;</td>
                            <td style="width: 80%; border: none;"><br>
                                <?php if($comment == 1){ 
                                    echo '<input type="checkbox" checked >
                                    &nbsp;&nbsp;&nbsp;
                                    All items indicated herein are in the APP
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="checkbox" >
                                    &nbsp;&nbsp;&nbsp;
                                    Some items indicated herein are NOT in the APP';
                                }else{
                                    echo '<input type="checkbox"  >
                                    &nbsp;&nbsp;&nbsp;
                                    All items indicated herein are in the APP
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="checkbox" checked="checked" >
                                    &nbsp;&nbsp;&nbsp;
                                    Some items indicated herein are NOT in the APP';
                                } 
                                ?>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <table class="table table-bordered">
                        <tr>
                            <td style="width: 20%">&nbsp;</td>
                            <td style="width: 20%">Requested By:</td>
                            <td style="width: 20%">Approved By:</td>
                            <td style="width: 20%">Issued By:</td>
                            <td style="width: 20%">Received By:</td>
                        </tr>
                        <tr>
                            <td>Signature:</td>
                            <td><br><br></td>
                            <td><br><br></td>
                            <td><br><br></td>
                            <td><br><br></td>
                        </tr>
                        <tr>
                            <td>Printed Name:</td>
                            <td><?= ucwords(strtoupper($model->requesterName)) ?></td>
                            <td><?= ucwords(strtoupper($model->approverName)) ?></td>
                            <td><?= ucwords(strtoupper($model->issuerName)) ?></td>
                            <td><?= ucwords(strtoupper($model->receiverName)) ?></td>
                        </tr>
                        <tr>
                            <td>Designation:</td>
                            <td><?= $model->requester ? $model->requester->position : '' ?></td>
                            <td><?= $model->approver ? $model->approver->position : '' ?></td>
                            <td><?= $model->issuer ? $model->issuer->POSITION_C : '' ?></td>
                            <td><?= $model->receiver ? $model->receiver->POSITION_C : '' ?></td>
                        </tr>
                        <tr>
                            <td>Date:</td>
                            <td><?= $model->requester ? $model->date_requested : '' ?></td>
                            <td><?= $model->approver ? $model->date_approved : '' ?></td>
                            <td><?= $model->issuer ? $model->date_issued : '' ?></td>
                            <td><?= $model->receiver ? $model->date_received : '' ?></td>
                        </tr>
                    </table>
                    <br>
                    <?php if($model->getItemsTotal('Realigned') > 0){ ?>
                        <h5 class="text-center"><b>SOURCE OF REALIGNMENT</b></h5>
                        <table class="table table-bordered table-condensed table-responsive">
                            <thead>
                                <tr>
                                    <th colspan=2>Description</th>
                                    <?php if($months){ ?>
                                        <?php foreach($months as $month){ ?>
                                            <th><?= $month->abbreviation ?></th>
                                        <?php } ?>
                                    <?php } ?>
                                    <th>Justification</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(!empty($realignedItems)){ ?>
                                <?php foreach($realignedItems as $activity => $subActivityItems){ ?>
                                    <tr>
                                        <th colspan=15><?= $activity ?></th>
                                    </tr>
                                    <?php if(!empty($subActivityItems)){ ?>
                                        <?php foreach($subActivityItems as $subActivity => $raItems){ ?>
                                            <tr>
                                                <th>&nbsp;</th>
                                                <th colspan=14><?= $subActivity ?></th>
                                            </tr>
                                            <?php if(!empty($raItems)){ ?>
                                                <?php foreach($raItems as $itemTitle => $ritems){ ?>
                                                    <tr>
                                                        <td>&nbsp;</td>
                                                        <td><?= $itemTitle ?></td>
                                                        <?php if($months){ ?>
                                                            <?php foreach($months as $month){ ?>
                                                                <td><?= isset($ritems[$month->id]) ? number_format($ritems[$month->id], 0) : 0 ?></td>
                                                            <?php } ?>
                                                        <?php } ?>
                                                        <td><?= $model->purpose ?></td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
  Modal::begin([
    'id' => 'view-item-modal',
    'size' => "modal-xl",
    'header' => '<div id="view-item-modal-header"><h4>View Item</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="view-item-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function viewItemDetails(id)
        {   
            $("#view-item-modal").modal("show").find("#view-item-modal-content").load("'.Url::to(['/v1/ppmp/view-item']).'?id=" + id);
        }   
    ';

    $this->registerJs($script, View::POS_END);
?>