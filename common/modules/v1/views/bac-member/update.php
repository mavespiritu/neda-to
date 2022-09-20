<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\BacMember */

$this->title = 'Update Bac Member';
$this->params['breadcrumbs'][] = ['label' => 'Bac Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="bac-member-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
