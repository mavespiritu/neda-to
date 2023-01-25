<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
use common\modules\v1\models\PpmpItem;
/* @var $form yii\widgets\ActiveForm */
?>
<div class="row">
    <div class="col-md-5 col-xs-12">
        <h3 class="panel-title">Item Information</h3>
        <br>
        <table class="table table-condensed table-hover table-responsive">
            <tr>
                <th>Name of Item</th>
                <td><?= $model->item->title ?></b></td>
            </tr>
            <tr>
                <th>Unit of measure</th>
                <td><?= $model->item->unit_of_measure ?></b></td>
            </tr>
        </table>
        <h3 class="panel-title">PPMP Information</h3>
        <br>
        <table class="table table-condensed table-hover table-responsive">
            <tr>
                <th>Cost Per Unit</th>
                <td><?= number_format($model->cost, 2) ?></td>
            </tr>
            <tr>
                <th>Object of Expense</th>
                <td><?= $model->obj->code.'<br>'.$model->obj->title ?></td>
            </tr>
            <tr>
                <th>Program</th>
                <td><?= $model->activity->pap->codeTitle.'<br>'.$model->activity->pap->title ?></td>
            </tr>
            <tr>
                <th>Activity</th>
                <td><?= $model->activityName ?></td>
            </tr>
            <tr>
                <th>Sub-Activity</th>
                <td><?= $model->subActivityName ?></td>
            </tr>
            <tr>
                <th>Fund Source</th>
                <td><?= $model->fundSourceName ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-7 col-xs-12">
        <h3 class="panel-title">Item Breakdown</h3>
        <br>
        <table class="table table-condensed table-hover table-responsive table-bordered">
            <thead>
                <tr>
                    <td align=center>&nbsp;</td>
                    <td align=center><b>J</b></td>
                    <td align=center><b>F</b></td>
                    <td align=center><b>M</b></td>
                    <td align=center><b>A</b></td>
                    <td align=center><b>M</b></td>
                    <td align=center><b>J</b></td>
                    <td align=center><b>J</b></td>
                    <td align=center><b>A</b></td>
                    <td align=center><b>S</b></td>
                    <td align=center><b>O</b></td>
                    <td align=center><b>N</b></td>
                    <td align=center><b>D</b></td>
                    <td align=center><b>Total</b></td>
                </tr>
            </thead>
            <tbody>
                <?php $ppmpTotal = 0; ?>
                <tr>
                    <td><b>PPMP</b></td>
                    <?php for($i = 1; $i < 13; $i++){ ?>
                        <td align=center><?= number_format($model->getQuantityPerMonth($i), 0) ?></td>
                        <?php $ppmpTotal += $model->getQuantityPerMonth($i) ?>
                    <?php } ?>
                    <td align=center><b><?= number_format($ppmpTotal, 0) ?></b></td>
                </tr>
                <tr>
                    <?= !empty($rises) ? '<td colspan=14><b>RIS Lined Up</b></td>' : '<td><b>RIS Lined Up</b></td><td colspan=13>No RIS</td>' ?>
                </tr>
                <?php if($rises){ ?>
                    <?php foreach($rises as $ris){ ?>
                        <?php $risTotal = 0; ?>
                        <tr>
                            <td><?= Html::a($ris->ris_no, ['/v1/ris/info', 'id' => $ris->id]) ?></td>
                            <?php for($i = 1; $i < 13; $i++){ ?>
                                <td align=center><?= number_format($model->getRisPerMonth($i, $ris->id), 0) ?></td>
                                <?php $risTotal += $model->getRisPerMonth($i, $ris->id) ?>
                            <?php } ?>
                            <td align=center><b><?= number_format($risTotal, 0) ?></b></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                <tr>
                    <?= !empty($realignedRises) ? '<td colspan=14><b>Realignment</b></td>' : '<td><b>Realignment</b></td><td colspan=13>No Realignment</td>' ?>
                </tr>
                <?php if($realignedRises){ ?>
                    <?php foreach($realignedRises as $ris){ ?>
                        <?php $realignedRisTotal = 0; ?>
                        <tr>
                            <td><?= Html::a($ris->ris_no, ['/v1/ris/info', 'id' => $ris->id]) ?></td>
                            <?php for($i = 1; $i < 13; $i++){ ?>
                                <td align=center><?= number_format($model->getRealignedRisPerMonth($i, $ris->id), 0) ?></td>
                                <?php $realignedRisTotal += $model->getRealignedRisPerMonth($i, $ris->id) ?>
                            <?php } ?>
                            <td align=center><b><?= number_format($realignedRisTotal, 0) ?></b></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                <tr>
                    <?= !empty($prs) ? '<td colspan=14><b>PR Lined Up</b></td>' : '<td><b>PR Lined Up</b></td><td colspan=13>No PR</td>' ?>
                </tr>
                <?php if($prs){ ?>
                    <?php foreach($prs as $pr){ ?>
                        <?php $prTotal = 0; ?>
                        <?php $awardedTotal = 0; ?>
                        <?php $obligatedTotal = 0; ?>
                        <?php $inspectedTotal = 0; ?>
                        <tr>
                            <td><?= Html::a($pr->pr_no, ['/v1/pr/view', 'id' => $pr->id]) ?></td>
                            <?php for($i = 1; $i < 13; $i++){ ?>
                                <td align=center><?= number_format($model->getPrPerMonth($i, $pr->id), 0) ?></td>
                                <?php $prTotal += $model->getPrPerMonth($i, $pr->id) ?>
                            <?php } ?>
                            <td align=center><b><?= number_format($prTotal, 0) ?></b></td>
                        </tr>
                        <tr>
                            <td align=right><i>Awarded</i></td>
                            <?php for($i = 1; $i < 13; $i++){ ?>
                                <td align=center><?= number_format($model->getPrAwardedPerMonth($i, $pr->id), 0) ?></td>
                                <?php $awardedTotal += $model->getPrAwardedPerMonth($i, $pr->id) ?>
                            <?php } ?>
                            <td align=center><b><?= number_format($awardedTotal, 0) ?></b></td>
                        </tr>
                        <tr>
                            <td align=right><i>Obligated</i></td>
                            <?php for($i = 1; $i < 13; $i++){ ?>
                                <td align=center><?= number_format($model->getPrObligatedPerMonth($i, $pr->id), 0) ?></td>
                                <?php $obligatedTotal += $model->getPrObligatedPerMonth($i, $pr->id) ?>
                            <?php } ?>
                            <td align=center><b><?= number_format($obligatedTotal, 0) ?></b></td>
                        </tr>
                        <tr>
                            <td align=right><i>Inspected</i></td>
                            <?php for($i = 1; $i < 13; $i++){ ?>
                                <td align=center><?= number_format($model->getPrInspectedPerMonth($i, $pr->id), 0) ?></td>
                                <?php $inspectedTotal += $model->getPrInspectedPerMonth($i, $pr->id) ?>
                            <?php } ?>
                            <td align=center><b><?= number_format($inspectedTotal, 0) ?></b></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
