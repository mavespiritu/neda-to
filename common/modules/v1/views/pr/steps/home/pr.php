<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
/* @var $model common\modules\v1\models\Pr */
/* @var $form yii\widgets\ActiveForm */
?>

<h3 class="panel-title">1.3 Preview PR</h3>

<h4 class="text-center"><b>PURCHASE REQUEST</b></h4>

<?php $form = ActiveForm::begin([
    'id' => 'print-pr-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<table class="table table-bordered table-responsive table-hover table-condensed">
    <thead>
        <tr>
            <td colspan=3><b>Entity Name: </b><u><?= $entityName['value'] ?></u></td>
            <td colspan=3><b>Fund Cluster: </b><u><?= $fundCluster->title ?></u></td>
        </tr>
        <tr>
            <td colspan=2 rowspan=2><b>Division: <?= $model->officeName ?></b></td>
            <td colspan=2><b>PR No.: <?= $model->pr_no ?></b></td>
            <td colspan=2 rowspan=2>
                <b>Date:</b>
                <?= $form->field($model, 'date_prepared')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                    'clientOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ])->label(false); ?>
            </td>
        </tr>
        <tr>
            <td colspan=2><b>Responsibility Center Code: <?= implode(",", $rccs ); ?></b></td>
        </tr>
        <tr>
            <td align=center><b>Stock/Property No.</b></td>
            <td align=center><b>Unit</b></th>
            <td align=center><b>Item Description</b></td>
            <td align=center><b>Quantity</b></td>
            <td align=center><b>Unit Cost</b></td>
            <td align=center><b>Total Cost</b></td>
        </tr>
    </thead>
    <tbody>
    <?php $total = 0; ?>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td><b><?= $model->purpose ?></b></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <?php if($model->lots){ ?>
        <?php if(!empty($lotItems)){ ?>
            <?php foreach($lotItems as $lot => $items){ ?>
                <?php if($lot != 0){ ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><b><?= $lot ?></b></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <?php } ?>
                <?php if(!empty($items)){ ?>
                    <?php foreach($items as $item){ ?>
                        <tr>
                            <td align=center><?= $item['item_id'] ?></td>
                            <td align=center><?= $item['unit'] ?></td>
                            <td><?= $item['item'] ?></td>
                            <td align=center><?= number_format($item['total'], 0) ?></td>
                            <td align=right><?= number_format($item['cost'], 2) ?></td>
                            <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
                        </tr>
                        <?php $total += $item['total'] * $item['cost'] ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php }else{ ?>
        <?php if(!empty($items)){ ?>
            <?php foreach($items as $item){ ?>
                <tr>
                    <td align=center><?= $item['item_id'] ?></td>
                    <td align=center><?= $item['unit'] ?></td>
                    <td><?= $item['item'] ?></td>
                    <td align=center><?= number_format($item['total'], 0) ?></td>
                    <td align=right><?= number_format($item['cost'], 2) ?></td>
                    <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
                </tr>
                <?php $total += $item['total'] * $item['cost'] ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    <?php if(!empty($specifications)){ ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td align=center><i>(Please see attached specifications for your reference)</i></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    <?php } ?>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align=center><b>xxxxxxxxxxxxxx NOTHING FOLLOWS xxxxxxxxxxxxxx</b></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align=right><b><?= number_format($total, 2) ?></b></td>
    </tr>
    <tr>
        <td colspan=6>Purpose: <?= $model->purpose ?></td>
    </tr>
    <tr>
        <td colspan=6>ABC: PHP <?= number_format($total, 2) ?></td>
    </tr>
    <tr>
        <td colspan=2>&nbsp;</td>
        <td>Requested by:</td>
        <td colspan=3>Approved by:</td>
    </tr>
    <tr>
        <td colspan=2>Signature:</td>
        <td>&nbsp;</td>
        <td colspan=3>&nbsp;</td>
    </tr>
    <tr>
        <td colspan=2>Printed Name:</td>
        <td><br><b><?= ucwords(strtoupper($model->requesterName)) ?></b></td>
        <td colspan=3><br><b><?= ucwords(strtoupper($model->approverName)) ?></b></td>
    </tr>
    <tr>
        <td colspan=2>Designation:</td>
        <td><?= $model->requester->position ?></td>
        <td colspan=3><?= $model->approver->position ?></td>
    </tr>
    </tbody>
</table>
<br>
<div class="pull-right">
    <?= Html::submitButton('<i class="fa fa-save"></i> Save PR', ['class' => 'btn btn-success']) ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>
<?php
    $script = '
        $("#print-pr-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("PR details has been saved.");
                    previewPr('.$model->id.');
                    manageItems('.$model->id.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });
    ';

    $this->registerJs($script, View::POS_END);
?>