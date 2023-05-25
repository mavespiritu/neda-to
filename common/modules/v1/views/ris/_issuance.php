<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use yii\web\View;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Ris */
/* @var $form yii\widgets\ActiveForm */
?>
<h4>Issue Items</h4>
<p>
    Issued by: <br><b><?= $issuance->issuerName ?></b> <br>
    Date Issued: <b><?= $issuance->issuance_date ?></b>
</p>
<?php $i = 1; ?>
<div class="issue-item-form">
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['/v1/ris/view-issuance', 'id' => $issuance->id]),
        'method' => 'POST',
    	'options' => ['class' => 'disable-submit-buttons'],
        'id' => 'issue-item-form',
    ]); ?>

    <table class="table table-bordered table-condensed table-hover">
        <thead>
            <tr>
                <td align=center style="width: 5%"><b>#</b></td>
                <td align=center style="width: 8%"><b>Stock No.</b></td>
                <td align=center style="width: 12%"><b>Unit</b></td>
                <td align=center style="width: 35%"><b>Description</b></td>
                <td align=center style="width: 10%"><b>Total Requested</b></td>
                <td align=center style="width: 10%"><b>Available</b></td>
                <td align=center style="width: 20%"><b>Total Issued</b></td>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($risItems)){ ?>
            <?php foreach($risItems as $activity => $subActivityitems){ ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <th colspan=2><?= $activity ?></th>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <?php if(!empty($subActivityitems)){ ?>
                    <?php foreach($subActivityitems as $subActivity => $items){ ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <th><?= $subActivity ?> - <?= $model->fundSource->code ?> Funded</th>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                        <?php if(!empty($items)){ ?>
                            <?php foreach($items as $item){ ?>
                                <?php $id = $item['id']; ?>
                                <tr>
                                    <td align=center><?= $i ?></td>
                                    <td align=center><?= $item['stockNo'] ?></td>
                                    <td align=center><?= $item['unitOfMeasure'] ?></td>
                                    <td><?= $item['itemTitle'] ?><br>
                                        <i><?= isset($specifications[$item['id']]) ? $specifications[$item['id']]->risItemSpecValueString : '' ?></i></td>
                                    <td align=center><?= number_format($item['total'], 0) ?></td>
                                    <td align=center><?= number_format($item['available'], 0) ?></td>
                                    <td><?= $form->field($issuanceItemModels[$item['id']], "[$id]quantity")->textInput(['type' => 'number', 'max' => $item['available'] + $issuanceItemModels[$item['id']]['quantity'], 'min' => 0])->label(false) ?></td>
                                </tr>
                                <?php $i++; ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>

    <div class="form-group pull-right">
        <?= Html::submitButton('Save Issuance', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
    $script = '
        $("#issue-item-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Issued items saved");
                    viewIssuance('.$issuance->id.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });';

    $this->registerJs($script, View::POS_END);
?>