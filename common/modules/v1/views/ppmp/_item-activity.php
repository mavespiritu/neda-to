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
<h3 class="panel-title">Activities</h3><br>
<div class="row">
<?php if(!empty($data)){ ?>
    <?php foreach($data as $fundSourceID => $fundSources): ?>
        <table class="table table-responsive table-condensed table-hover">
            <tr>
                <td colspan=4 align="center"><b><?= $fundSources['title'] ?></b></td>
            </tr>
            <?php if(!empty($fundSources['contents'])){ ?>
                <?php foreach($fundSources['contents'] as $activityID => $activities): ?>
                    <tr>
                        <td><a javascript:void(0); onclick="loadItems(<?= $model->id ?>, <?= $activityID ?>, <?= $fundSourceID ?>)" style="cursor: pointer;"><?= $activities['title'] ?></a></td>
                    </tr>
                <?php endforeach ?>
            <?php } ?>
        </table>
    <?php endforeach ?>
<?php } ?>
</div>