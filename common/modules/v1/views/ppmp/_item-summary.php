<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
use common\modules\v1\models\PpmpItem;
/* @var $form yii\widgets\ActiveForm */
?>
<br>
<div class="row">
<?php if(!empty($data)){ ?>
    <?php foreach($data as $fundSourceID => $fundSources): ?>
        <div class="col-md-6 col-xs-12">
            <table class="table table-responsive table-condensed table-hover">
            <tr>
                <td colspan=4 align="center"><b><?= $fundSources['title'] ?></b></td>
            </tr>
            <?php if(!empty($fundSources['contents'])){ ?>
                <?php foreach($fundSources['contents'] as $activityID => $activities): ?>
                    <tr>
                        <td colspan=3><a javascript:void(0); onclick="loadItems(<?= $model->id ?>, <?= $activityID ?>, <?= $fundSourceID ?>)" style="cursor: pointer;"><?= $activities['title'] ?></a></td>
                        <td align=right><?= number_format(PpmpItem::getTotalPerActivity($model->id, $activityID, $fundSourceID), 2) ?></td>
                    </tr>
                    <?php if(!empty($activities['contents'])){ ?>
                        <?php foreach($activities['contents'] as $subActivityID => $subActivities): ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td style="font-size: 12px;" colspan=2> <?= $subActivities['title'] ?></td>
                                <td style="font-size: 12px;" align=right><?= number_format(PpmpItem::getTotalPerSubActivity($model->id, $activityID, $subActivityID, $fundSourceID), 2) ?></td>
                            </tr>
                            <?php //if(!empty($subActivities['contents'])){ ?>
                                <?php //foreach($subActivities['contents'] as $objectID => $object): ?>
                                    <!-- <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="font-size: 11px;"> <?php //$object['title'] ?></td>
                                        <td style="font-size: 11px;" align=right><?php //number_format($object['total'], 2) ?></td>
                                    </tr> -->
                                <?php //endforeach ?>
                            <?php //} ?>
                        <?php endforeach ?>
                    <?php } ?>
                <?php endforeach ?>
            <?php } ?>
            </table>
        </div>
    <?php endforeach ?>
<?php } ?>
</div>