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
<h3 class="panel-title">3.1 Request APR</h3>
<br>
<?php $form = ActiveForm::begin([
    'id' => 'apr-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>
<table class="table table-bordered table-responsive table-hover table-condensed">
    <thead>
        <tr>
            <td rowspan=2 colspan=3>NAME & ADDRESS <br> OF REQUESTING <br> AGENCY <br><br></td>
            <td rowspan=2 colspan=2><b><?= $agency->value ?></b><br><?= $regionalOffice->value ?><br><?= $address->value ?> <br><br></td>
            <td colspan=3 style="vertical-align: bottom;">ACC. CODE: </td>
        </tr>
        <tr>
            <td colspan=3 style="vertical-align: bottom;">Agency Control No. <br><?= $model->pr_no ?></td>
        </tr>
        <tr>
            <td colspan=5 style="vertical-align: bottom;" colspan=2 align=center><b>AGENCY PROCUREMENT REQUEST</b></td>
            <td colspan=3 style="vertical-align: bottom;">PS APR No.</td>
        </tr>
        <tr>
            <td colspan=5 style="width: 75%;">
                <p>
                    TO: <br>
                    <?= $supplier->business_name ?> <br>
                    <?= $supplier->business_address ?> <br>
                </p>
                <p style="text-align: center;">ACTION REQUEST ON THE ITEM(S) LISTED BELOW</p>
                <table style="width: 100%; border-collapse: collapse; border-spacing: 0;" cellspacing=0>
                    <tr>
                        <td style="vertical-align: top;">
                            <?= $form->field($aprModel, 'checklist_1')->checkbox(['id' => 'apr-check_1', 'class' => 'apr-checklist', 'checked' => 'checked', 'value' => is_null($aprModel->checklist_1) ? '0' : $aprModel->checklist_1, 'onclick' => '$(this).attr("value", this.checked ? 1 : 0)', 'label' => ''])->label(false) ?></td>
                        <td style="vertical-align: top; padding-left: 5px;">Please furnish us with Price Estimate (for office equipment/furniture & supplementary items)</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <?= $form->field($aprModel, 'checklist_2')->checkbox(['id' => 'apr-check_2', 'class' => 'apr-checklist', 'checked' => 'checked', 'value' => is_null($aprModel->checklist_2) ? '0' : $aprModel->checklist_2, 'onclick' => '$(this).attr("value", this.checked ? 1 : 0)', 'label' => ''])->label(false) ?></td>
                        <td style="vertical-align: top; padding-left: 5px;">
                        Please purchase for our agency the equipment/furniture/supplementary items per your Price Estimate (PS RAD No. <?= Html::input('text', 'Apr[rad_no]', $aprModel->rad_no, ['id' => 'apr-rad_no']) ?> attached) dated 
                        <?= Html::input('text', 'Apr[rad_month]', $aprModel->rad_month, ['id' => 'apr-rad_month', 'placeholder' => 'Month', 'style' => 'width: 100px;']) ?> - <?= Html::input('text', 'Apr[rad_year]', $aprModel->rad_year, ['id' => 'apr-rad_year', 'placeholder' => 'Year', 'style' => 'width: 80px;', 'type' => 'number', 'min' => (date("Y") - 1)]) ?>
                        <br>
                        <br>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <?= $form->field($aprModel, 'checklist_3')->checkbox(['id' => 'apr-check_3', 'class' => 'apr-checklist', 'checked' => 'checked', 'value' => is_null($aprModel->checklist_3) ? '0' : $aprModel->checklist_3, 'onclick' => '$(this).attr("value", this.checked ? 1 : 0)', 'label' => ''])->label(false) ?>
                        </td>
                        <td style="vertical-align: top; padding-left: 5px;">
                        Please issue common-use supplies/materials per PS Price List as of <?= Html::input('text', 'Apr[pl_month]', $aprModel->pl_month, ['id' => 'apr-pl_month', 'placeholder' => 'Month', 'style' => 'width: 100px;']) ?> - <?= Html::input('text', 'Apr[pl_year]', $aprModel->pl_year, ['id' => 'apr-pl_year', 'placeholder' => 'Year', 'style' => 'width: 80px;', 'type' => 'number', 'min' => (date("Y") - 1)]) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <?= $form->field($aprModel, 'checklist_4')->checkbox(['id' => 'apr-check_4', 'class' => 'apr-checklist', 'checked' => 'checked', 'value' => is_null($aprModel->checklist_4) ? '0' : $aprModel->checklist_4, 'onclick' => '$(this).attr("value", this.checked ? 1 : 0)', 'label' => ''])->label(false) ?>
                        </td>
                        <td style="vertical-align: top; padding-left: 5px;">
                        Please issue Certificate of Price Reasonableness
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <?= $form->field($aprModel, 'checklist_5')->checkbox(['id' => 'apr-check_5', 'class' => 'apr-checklist', 'checked' => 'checked', 'value' => is_null($aprModel->checklist_5) ? '0' : $aprModel->checklist_5, 'onclick' => '$(this).attr("value", this.checked ? 1 : 0)', 'label' => ''])->label(false) ?>
                        </td>
                        <td style="vertical-align: top; padding-left: 5px;">
                        Please furnish us with your latest/updated Price list
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <?= $form->field($aprModel, 'checklist_6')->checkbox(['id' => 'apr-check_6', 'class' => 'apr-checklist', 'checked' => 'checked', 'value' => is_null($aprModel->checklist_6) ? '0' : $aprModel->checklist_6, 'onclick' => '$(this).attr("value", this.checked ? 1 : 0)', 'label' => ''])->label(false) ?>
                        </td>
                        <td style="vertical-align: top; padding-left: 5px;">
                        Others (specify) <?= Html::input('text', 'Apr[other]', $aprModel->others, ['id' => 'apr-other', 'style' => 'width: 200px;']) ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td colspan=3 style="text-align: center; width: 25%;">
            <?= $form->field($aprModel, 'date_prepared')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off', 'id' => 'apr-date_generated',],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ])->label(false); ?>
            <br>
                <i>(Date Prepared)</i>
            </td>
        </tr>
        <tr>
            <td align=center colspan=8>IMPORTANT! PLEASE SEE INSTRUCTIONS/CONDITIONS AT THE BACK OF ORIGINAL COPY</td>
        </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
        <tr>
            <td align=center style="width: 5%;"><b>No.</b></td>
            <td align=center colspan=3 style="width: 50%;"><b>ITEM and DESCRIPTION/SPECIFICATIONS/STOCK No.</b></td>
            <td align=center style="width: 10%;"><b>QUANTITY</b></td>
            <td align=center style="width: 10%;"><b>UNIT</b></td>
            <td align=center style="width: 10%;"><b>UNIT PRICE</b></td>
            <td align=center style="width: 10%;"><b>AMOUNT</b></td>
        </tr>
        <?php if(!empty($aprItems)){ ?>
            <?php foreach($aprItems as $item){ ?>
                <tr>
                    <td align=center><?= $i ?></td>
                    <td colspan=3><?= $item['item'] ?><br>
                    <?php if(isset($specifications[$item['id']])){ ?>
                        <?php if(!empty($specifications[$item['id']]->risItemSpecFiles)){ ?>
                        <table style="width: 100%">
                        <?php foreach($specifications[$item['id']]->risItemSpecFiles as $file){ ?>
                            <tr>
                            <td><?= Html::a($file->name.'.'.$file->type, ['/file/file/download', 'id' => $file->id]) ?></td>
                            <!-- <td align=right><?= Html::a('<i class="fa fa-trash"></i>', ['/file/file/delete', 'id' => $file->id], [
                                    'data' => [
                                        'confirm' => 'Are you sure you want to remove this item?',
                                        'method' => 'post',
                                    ],
                                ]) ?></td> -->
                            </tr>
                        <?php } ?>
                        </table>
                        <br>
                        <?php } ?>
                        <?= $specifications[$item['id']]->risItemSpecValueString ?>
                    <?php } ?>
                    </td>
                    <td align=center><?= number_format($item['total'], 0) ?></td>
                    <td align=center><?= $item['unit'] ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <?php $i++; ?>
            <?php } ?>
        <?php } ?>
        <?php if(!empty($specifications)){ ?>
        <tr>
            <td>&nbsp;</td>
            <td colspan=3 align=center>(Please see attached specifications for your reference.)</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <?php } ?>
        <tr>
            <td>&nbsp;</td>
            <td colspan=3 align=center>xxxxxxxxxxxxxx NOTHING FOLLOWS xxxxxxxxxxxxxxx</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan=4><?= $shortName->value ?> Office Telefax No: <?= Html::input('text', 'Apr[telefax]', $aprModel->telefax, ['id' => 'apr-telefax']) ?></td>
            <td colspan=2 align=right>Total AMOUNT:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan=8 align=center>NOTE: ALL SIGNATURES MUST BE OVER PRINTED NAME</td>
        </tr>
    </tbody>
</table>
<table class="table table-bordered table-responsive table-hover table-condensed">
    <tr>
        <td style="width: 30%">
            STOCKS REQUESTED ARE CERTIFIED <br>
            TO BE WITHIN APPROVED PROGRAM: <br>
            <br>
            <br>
            <p style="text-align: center"><b><?= strtoupper($aprModel->stockCertifierName) ?></b><br><?= $aprModel->stockCertifier ? $aprModel->stockCertifier->position.' (Supply Officer)' : '' ?></p>
        </td>
        <td style="width: 30%">
            FUNDS CERTIFIED AVAILABLE:
            <br>
            <br>
            <br>
            <br>
            <p style="text-align: center"><b><?= strtoupper($aprModel->fundsCertifierName) ?></b><br><?= $aprModel->fundsCertifier ? $aprModel->fundsCertifier->position : '' ?></p>
        </td>
        <td style="width: 30%">
            APPROVED:
            <br>
            <br>
            <br>
            <br>
            <p style="text-align: center"><b><?= strtoupper($aprModel->approverName) ?></b><br><?= $aprModel->approver ? $aprModel->approver->position : '' ?></p>
        </td>
    </tr>
</table>
<br>
<div class="pull-right">
<?= Html::submitButton('<i class="fa fa-print"></i> Save and Print', ['class' => 'btn btn-success']) ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>
<?php
    $script = '
        function putValueInCheckbox()
        {
            $(".apr-checklist").each(function(e){
                if($(this).val() == 1){
                    $(this).attr("checked", "checked");
                }
            });
        }

        $("#apr-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    printApr('.$model->id.');
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });

        function printApr(id)
        {
            var printWindow = window.open(
            "'.Url::to(['/v1/pr/print-apr']).'?id=" + id, 
            "Print",
            "left=200", 
            "top=200", 
            "width=650", 
            "height=500", 
            "toolbar=0", 
            "resizable=0"
            );
            printWindow.addEventListener("load", function() {
                printWindow.print();
                setTimeout(function() {
                printWindow.close();
            }, 1);
            }, true);
        }

        $(document).ready(function(){
            $(".apr-checklist").removeAttr("checked");
            putValueInCheckbox();
        });
    ';

    $this->registerJs($script, View::POS_END);
?>