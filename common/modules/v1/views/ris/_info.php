<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Ris */

$this->title = $model->status ? $model->ris_no.' ['.$model->status->status.']' : $model->ris_no;
$this->params['breadcrumbs'][] = ['label' => 'RIS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $i = 1; ?>
<div class="ris-view">
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>

    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> RIS Information</div>
                <?php if($model->status->status == 'For Revision'){ ?> <div class="alert alert-info">Remarks: <?= $model->status->remarks ?></div> <?php } ?>
                <div class="box-body">
                    <span>
                        <?php // Html::a('<i class="fa fa-print"></i> Print', ['#'],['class' => 'btn btn-danger']) ?>
                    </span>
                    <br>
                    <h5 class="text-center"><b>REQUEST AND ISSUANCE SLIP</b></h5>
                    <p><b>Entity Name: <u><?= $entityName ?></u></b></p>
                    <p><b>Fund Cluster: <u><?= $fundClusterName ?></u></b></p>
                    <?php $total = 0; ?>
                    <table class="table table-bordered">
                        <tr>
                            <td colspan=2>Division:<br>Office: </td>
                            <td colspan=4><u><?= $model->officeName ?></u><br><u><?= $model->officeName ?></u></td>
                            <td colspan=5>RIS No. <u><?= $model->ris_no ?></u></td>
                        </tr>
                        <tr>
                            <td colspan=6 align=center><b>Requisition</b></td>
                            <td rowspan=2><b>Stock Available?</b></td>
                            <td colspan=4 align=center><b>Issue</b></td>
                        </tr>
                        <tr>
                            <td align=center>#</td>
                            <td align=center>Stock No.</td>
                            <td align=center>Unit</td>
                            <td align=center>Description</td>
                            <td align=center>Quantity</td>
                            <td align=center>ABC</td>
                            <td align=center>Quantity</td>
                            <td align=center>Date Issue</td>
                            <td align=center>Remarks</td>
                            <td align=center>Fund Source</td>
                        </tr>
                        <?php if(!empty($risItems)){ ?>
                            <?php foreach($risItems as $idx => $items){ ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <th><?= $idx ?> - <?= $model->fundSource->code ?> Funded</th>
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
                        <tr>
                            <td colspan=5 align=right><b>Total</b></td>
                            <td align=right><b><?= number_format($total, 2) ?></b></td>
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
                            <td>&nbsp;</td>
                            <td>Requested By:</td>
                            <td>Approved By:</td>
                            <td>Issued By:</td>
                            <td>Received By:</td>
                        </tr>
                        <tr>
                            <td>Signature:</td>
                            <td><br><br></td>
                            <td><br><br></td>
                            <td><br><br></td>
                            <td><br><br></td>
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
                        <table class="table table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th>Description</th>
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
                                <?php foreach($realignedItems as $activity => $raItems){ ?>
                                    <tr>
                                        <th colspan=14><?= $activity ?></th>
                                    </tr>
                                    <?php if(!empty($raItems)){ ?>
                                        <?php foreach($raItems as $itemTitle => $ritems){ ?>
                                            <tr>
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
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>