<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Ris */

$this->title = $model->status ? $model->ris_no.' ['.$model->status->status.']' : $model->ris_no;
$this->params['breadcrumbs'][] = ['label' => 'RIS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$total = 0;
?>

<div class="ris-view">
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>

    <div class="row">
        <div class="col-md-5 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-edit"></i> Add Supplemental Item Form</div>
                <div class="box-body">
                    <?= $this->render('_supplemental-item-form', [
                        'model' => $model,
                        'itemModel' => $itemModel,
                        'activities' => $activities,
                        'subActivities' => $subActivities,
                        'objects' => $objects,
                        'items' => $items,
                        'months' => $months,
                        'itemBreakdowns' => $itemBreakdowns,
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-7 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i>RIS Supplemental Items</div>
                <div class="box-body">
                <table class="table table-bordered table-condensed table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%">&nbsp;</th>
                                <th>&nbsp;</th>
                                <th style="width: 25%">Item</th>
                                <th style="width: 15%">Specification</th>
                                <td align=center style="width: 15%"><b>Quantity</b></td>
                                <td align=right style="width: 15%"><b>Unit Cost</b></td>
                                <td align=right style="width: 15%"><b>Total</b></td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($supplementalItems)){ ?>
                            <?php $i = 1; ?>
                            <?php foreach($supplementalItems as $activity => $activityItems){ ?>
                                <tr>
                                    <th colspan=7><?= $activity ?></th>
                                </tr>
                                <?php if(!empty($activityItems)){ ?>
                                    <?php foreach($activityItems as $subActivity => $subActivityItems){ ?>
                                        <tr>
                                            <th>&nbsp;</th>
                                            <th colspan=6><?= $subActivity ?></th>
                                        </tr>
                                        <?php if(!empty($subActivityItems)){ ?>
                                            <?php foreach($subActivityItems as $item){ ?>
                                                <?= $this->render('_item', [
                                                    'i' => $i,
                                                    'model' => $model,
                                                    'item' => $item,
                                                    'specifications' => $specifications,
                                                    'type' => 'Supplemental'
                                                ]) ?>
                                                <?php $total += ($item['cost'] * $item['total']); ?>
                                                <?php $i++; ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php }else{ ?>
                            <tr>
                                <td colspan=7 align=center>No supplemental items included</td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td colspan=6 align=right><b>Grand Total</b></td>
                            <td align=right><b><?= number_format($total, 2) ?></b></td>
                        </tr>
                        <tr>
                            <td colspan=6 align=right><b>Realigned</b></td>
                            <td align=right><b><?= number_format($model->getItemsTotal('Realigned'), 2) ?></b></td>

                        </tr>
                        <tr>
                            <td colspan=6 align=right><b>Unused</b></td>
                            <td align=right><b><?= number_format($model->getItemsTotal('Realigned') - $total, 2) ?></b></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>