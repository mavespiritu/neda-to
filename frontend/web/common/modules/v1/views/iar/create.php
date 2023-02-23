<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Iar */

$this->title = 'Create Iar';
$this->params['breadcrumbs'][] = ['label' => 'Iars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="iar-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
