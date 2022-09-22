<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
/* @var $model common\modules\v1\models\PpmpSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ppmp-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'stage')->widget(Select2::classname(), [
                    'data' => ['' => 'All Stages'] + $stages,
                    'options' => ['multiple' => false, 'class'=>'stage-select'],
                    'pluginOptions' => [
                        'allowClear' =>  false,
                    ],
                ]);
            ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'year')->widget(Select2::classname(), [
                    'data' => ['' => 'All Years'] + $years,
                    'options' => ['multiple' => false, 'class'=>'year-select'],
                    'pluginOptions' => [
                        'allowClear' =>  false,
                    ],
                ]);
            ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'creatorName') ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'statusName')->widget(Select2::classname(), [
                'data' => ['Approved' => 'Approved', 'Draft' => 'Draft', 'Disapproved' => 'Disapproved', 'For Revision' => 'For Revision'],
                'options' => ['placeholder' => 'Select Status','multiple' => false, 'class'=>'fund-source-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>
        </div>
    </div>
    <div class="row">
        <?php if(Yii::$app->user->can('Administrator') || Yii::$app->user->can('AccountingStaff')){ ?>
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
    </div>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Clear', ['class' => 'btn btn-outline-secondary', 'onClick' => 'redirectPage()']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$script = '
    function redirectPage()
    {
        window.location.href = "'.Url::to(['/v1/ppmp/']).'";
    }
';
$this->registerJs($script, View::POS_END);
?>