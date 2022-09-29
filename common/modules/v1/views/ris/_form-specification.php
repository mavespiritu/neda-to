<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;

?>

<div class="specification-form">

    <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>
    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
        'widgetBody' => '.container-items', // required: css class selector
        'widgetItem' => '.item', // required: css class
        'min' => 1, // 0 or 1 (default 1)
        'insertButton' => '.add-item', // css class
        'deleteButton' => '.remove-item', // css class
        'model' => $specValues[0],
        'formId' => 'dynamic-form',
        'formFields' => [
            'description',
            'value',
        ],
    ]); ?>

<?= $form->field($spec, 'activity_id')->hiddenInput(['value' => $activity->id])->label(false) ?>
<?= $form->field($spec, 'sub_activity_id')->hiddenInput(['value' => $subActivity->id])->label(false) ?>

    <table class="table table-condensed">
        <tr>
            <th>RIS No.</th>
            <td><?= $model->ris_no ?></td>
        </tr>
        <tr>
            <th>Activity</th>
            <td><?= $activity->title ?></td>
        </tr>
        <tr>
            <th>PPA</th>
            <td><?= $subActivity->title ?> - <?= $model->fundSource->code ?> Funded</td>
        </tr>
        <tr>
            <th>Item</th>
            <td><?= $item->title ?></td>
        </tr>
    </table>
    <p>List down all specification of item below. Click "Add Specification" button to add more row.
    <div class="pull-right"><button type="button" class="pull-right add-item btn btn-success btn-xs"><i class="fa fa-plus"></i> Add Descriptions</button></div>
    <div class="clearfix"></div>
    <br>
    <table class="table table-responsive table-condensed">
        <thead>
            <tr>
                <th>Description</th>
                <th>Value</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody class="container-items">
        <?php foreach ($specValues as $i => $specValue): ?>
            <tr class="item">
                <td><?= $form->field($specValue, "[{$i}]description")->textInput(['maxlength' => true, 'placeholder' => 'e.g. Color'])->label(false) ?></td>
                <td><?= $form->field($specValue, "[{$i}]value")->textInput(['maxlength' => true, 'placeholder' => 'e.g. Black'])->label(false) ?></td>
                <td><button type="button" class="pull-right remove-item btn btn-danger btn-xs"><i class="fa fa-minus"></i></button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php DynamicFormWidget::end(); ?>

    <div class="form-group pull-right">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <div class="clearfix"></div>

    <?php ActiveForm::end(); ?>

</div>