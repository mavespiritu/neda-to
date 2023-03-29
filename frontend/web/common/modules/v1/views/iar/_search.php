<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\IarSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="iar-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'pr_id') ?>

    <?= $form->field($model, 'po_id') ?>

    <?= $form->field($model, 'iar_no') ?>

    <?= $form->field($model, 'iar_date') ?>

    <?php // echo $form->field($model, 'invoice_no') ?>

    <?php // echo $form->field($model, 'invoice_date') ?>

    <?php // echo $form->field($model, 'inspected_by') ?>

    <?php // echo $form->field($model, 'date_inspected') ?>

    <?php // echo $form->field($model, 'received_by') ?>

    <?php // echo $form->field($model, 'date_received') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
