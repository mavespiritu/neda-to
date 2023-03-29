<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Iar */

$this->title = 'Update Iar';
$this->params['breadcrumbs'][] = ['label' => 'Iars', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="iar-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
