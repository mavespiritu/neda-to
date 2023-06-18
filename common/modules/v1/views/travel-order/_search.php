<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\TravelOrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="travel-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'TO_NO') ?>

    <?= $form->field($model, 'date_filed') ?>

    <?= $form->field($model, 'TO_creator') ?>

    <?= $form->field($model, 'TO_subject') ?>

    <?= $form->field($model, 'date_from') ?>

    <?php // echo $form->field($model, 'date_to') ?>

    <?php // echo $form->field($model, 'withVehicle') ?>

    <?php // echo $form->field($model, 'isDirector_Approved') ?>

    <?php // echo $form->field($model, 'type_of_travel') ?>

    <?php // echo $form->field($model, 'otherpassenger') ?>

    <?php // echo $form->field($model, 'othervehicle') ?>

    <?php // echo $form->field($model, 'otherdriver') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
