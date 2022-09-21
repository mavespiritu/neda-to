<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Ppmp */

$this->title = 'GAA '.$model->year;
$this->params['breadcrumbs'][] = ['label' => 'GAAs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$columnTotals = [];
?>
<div class="nep-view">
    <?= $this->render('_menu', ['model' => $model]) ?>
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> Manage Allocation</div>
                <div class="box-body">
                    <p><i class="fa fa-exclamation-circle"></i> Just input the amounts. Data are autosaved.</p>
                    <div class="appropriation-table" style="max-height: 800px;">
                    <table class="table table-responsive table-striped table-condensed table-hover table-bordered" id="main-table" style="padding: 20px auto;">
                        <thead>
                            <tr>
                                <th style="width: <?= (count($model->appropriationPaps) + 1)/100 ?>%;">Objects</th>
                                <?php if($model->appropriationPaps): ?>
                                    <?php foreach($model->getAppropriationPaps()->orderBy(['arrangement'=> SORT_ASC])->all() as $program): ?>
                                        <th style="width: <?= (count($model->appropriationPaps) + 1)/100 ?>%;">
                                            <?= $program->fundSource->code ?>
                                            <hr style="opacity: 0.3">
                                            <p><span style="font-size: 12px;"><?= $program->pap->title ?></span><br>
                                            <?= $program->pap->codeTitle ?>
                                            </p>
                                        </th>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($model->appropriationObjs): ?>
                            <?php foreach($model->getAppropriationObjs()->orderBy(['arrangement'=> SORT_ASC])->all() as $object): ?>
                                <?php $rowTotal = 0; ?>
                                <tr>
                                    <td><?= $object->obj->objectTitle ?></td>
                                    <?php if(!empty($items)): ?>
                                        <?php $id = 0; ?>
                                        <?php foreach($items[$object->obj_id] as $key => $objectItem): ?>
                                            <?php $columnTotals[$id] = isset($columnTotals[$id]) ? $columnTotals[$id] : 0 ?>
                                                <?php $form = ActiveForm::begin([
                                                    'id' => $objectItem->obj_id.$key,
                                                    'method' => 'POST',
                                                    'class' => 'nep-form',
                                                ]); ?>
                                                <td>
                                                    <?= Html::activeHiddenInput($objectItem, 'idx', ['value' => $key]) ?>
                                                    <?= $form->field($objectItem, "[$key]obj_id")->hiddenInput(['value' => $objectItem->obj_id])->label(false) ?>
                                                    <?= $form->field($objectItem, "[$key]pap_id")->hiddenInput(['value' => $objectItem->pap_id])->label(false) ?>
                                                    <?= $form->field($objectItem, "[$key]fund_source_id")->hiddenInput(['value' => $objectItem->fund_source_id])->label(false) ?>
                                                    <?= $form->field($objectItem, "[$key]amount")->widget(MaskedInput::classname(), [
                                                        'options' => [
                                                            'id' => $objectItem->obj_id.$key.'-input',
                                                            'class' => 'form-control amount-select',
                                                            'onkeypress' => 'submitForm($("#'.$objectItem->obj_id.$key.'"));',
                                                            'onFocusout' => 'submitForm($("#'.$objectItem->obj_id.$key.'"));',
                                                            'autocomplete' => 'off',
                                                        ],
                                                        'clientOptions' => [
                                                            'alias' =>  'decimal',
                                                            'removeMaskOnSubmit' => true,
                                                            'groupSeparator' => ',',
                                                            'autoGroup' => true
                                                        ],
                                                    ])->label(false) ?>
                                                </td>
                                                <?php ActiveForm::end(); ?>
                                                <?php $rowTotal += $objectItem->amount; ?>
                                                <?php $columnTotals[$id] += $objectItem->amount; ?>
                                                <?php $id++ ?>
                                        <?php endforeach ?> 
                                    <?php endif ?>
                                    <td align=right style="padding-top: 25px;"><b><?= number_format($rowTotal, 2) ?></b></td>
                                </tr>
                            <?php endforeach ?>
                                <?php $grandTotal = 0; ?>
                                <tr>
                                    <td><b>Total</b></td>
                                    <?php if(!empty($columnTotals)){ ?>
                                        <?php foreach($columnTotals as $columnTotal){ ?>
                                            <td align=right><b><?= number_format($columnTotal, 2) ?></b></td>
                                            <?php $grandTotal += $columnTotal ?>
                                        <?php } ?>
                                    <?php } ?>
                                    <td align=right><b><?= number_format($grandTotal, 2) ?></b></td>
                                </tr>
                        <?php endif ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>  
    </div>
</div>
<?php
  $script = '
    function submitForm(form)
    {
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                
            },
            error: function (err) {
                console.log(err);
            }
        });
    }
    $(document).ready(function() {
        $(".appropriation-table").freezeTable({
            "scrollable": true,
        });
    });
  ';
  $this->registerJs($script, View::POS_END);
?>
