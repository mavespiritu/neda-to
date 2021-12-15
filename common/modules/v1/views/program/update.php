<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Program */

$this->title = 'Update Program';
$this->params['breadcrumbs'][] = ['label' => 'Programs', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="program-update">

    <?= $this->render('_form', [
        'model' => $model,
        'costStructures' => $costStructures,
        'organizationalOutcomes' => $organizationalOutcomes,
    ]) ?>

</div>
