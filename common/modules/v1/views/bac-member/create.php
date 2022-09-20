<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\BacMember */

$this->title = 'Create Bac Member';
$this->params['breadcrumbs'][] = ['label' => 'Bac Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bac-member-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
