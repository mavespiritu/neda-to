<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\web\View;
/* @var $model common\modules\v1\models\PpmpSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ris-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    
    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'ris_no') ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'type')->widget(Select2::classname(), [
                'data' => $types,
                'options' => ['placeholder' => 'Select Type','multiple' => false, 'class'=>'type-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ])->label('Type');
            ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'fund_source_id')->widget(Select2::classname(), [
                'data' => $fundSources,
                'options' => ['placeholder' => 'Select Fund Source','multiple' => false, 'class'=>'fund-source-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'date_required')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <?php if(Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('AccountingStaff')){ ?>
            <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'office_id')->widget(Select2::classname(), [
                'data' => ['' => 'All Divisions'] + $offices,
                'options' => ['multiple' => false, 'class'=>'office-select'],
                'pluginOptions' => [
                    'allowClear' =>  false,
                ],
            ]);
            ?>
            </div>
        <?php } ?>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'creatorName') ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'requesterName') ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'statusName')->widget(Select2::classname(), [
                'data' => ['Approved' => 'Approved', 'Draft' => 'Draft', 'Disapproved' => 'Disapproved', 'For Approval' => 'For Approval', 'For Revision' => 'For Revision'],
                'options' => ['placeholder' => 'Select Status','multiple' => false, 'class'=>'fund-source-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'prNo') ?>
        </div>
        <div class="col-md-9 col-xs-12">
            <?= $form->field($model, 'purpose')->textarea(['rows' => 1]) ?>
        </div>
    </div>
    

    <div class="form-group pull-right">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Clear', ['class' => 'btn btn-outline-secondary', 'onClick' => 'redirectPage()']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$script = '
    function redirectPage()
    {
        window.location.href = "'.Url::to(['/v1/ris/']).'";
    }
';
$this->registerJs($script, View::POS_END);
?>