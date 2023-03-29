<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use dosamigos\ckeditor\CKEditor;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use frontend\assets\AppAsset;

$asset = AppAsset::register($this);
?>
<h3 class="panel-title"><?= $j ?>.<?= $i ?>.<?= $k ?> Preview Contract for Supplier, <?= $supplier->business_name ?>
<span class="pull-right">
<?= !$contractModel->isNewRecord ? Html::a('<i class="fa fa-print"></i> Print', null, ['href' => 'javascript:void(0)', 'onClick' => 'printPo('.$contractModel->id.')', 'class' => 'btn btn-info']) : '' ?>
</span></h3>
<br>
<br>
<?php $form = ActiveForm::begin([
    'id' => 'contract-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>
<?= $form->field($contractModel, 'po_no')->textInput(['maxlength' => true, 'style' => 'width: 300px;'])->label('Contract No.') ?>
<div class="contract-content">
    <p><b>KNOW ALL MEN BY THESE PRESENTS:</b></p>
    <br>
    <p>This agreement made and entered into this <br>
    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= $form->field($contractModel, 'po_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ])->label('Date of Contract'); ?>
        </div>
    </div>
    by and between the <b><?= $agency->value ?> REGIONAL OFFICE 1</b>, represented by the Regional Director, <b><?= $rd->value ?></b>, hereinafter known as the party of the first part and <b><?= $supplier->business_name ?></b> represented by <b><?= $supplier->owner_name ?></b>, hereinafter known as the party of the second part.</p>
    <p class="text-center"><b>W I T N E S S E T H:</b></p>
    <p>1. That the Party of the Second Part shall provide the following:</p>
    <p><b><?= $model->purpose ?>:</b></p>
    <?= $form->field($contractModel, 'content')->widget(CKEditor::className(), [
        'options' => ['rows' => 3],
        'preset' => 'full'
    ])->label(false) ?>
    <p>2. That the Party of the First Part shall pay the Party of the Second Part in Philippine Currency the amount of <b><?= strtoupper(Yii::$app->controller->module->getNumberToWords(sprintf('%0.2f', $total['total']))) ?> (Php <?= number_format($total['total'], 2) ?>) ONLY</b> upon satisfactory completion of the service contracted for.</p>
    <p>3. That this Contract shall automatically cease to be of any force and effect when sooner terminated at
the option of any or both parties. In such case, payment shall be made on the basis of percentage of service
completed.</p>
    <p><b>IN WITNESS WHEREOF</b>, both parties sign this Agreement this <b>17th day of October 2022</b> at the <?= ucwords(strtolower($agency->value))?> Regional Office 1, <?= $address->value ?>.</p>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <i><u>PARTY OF THE FIRST PART</u></i>
            <br>
            <br>
            <b><?= $agency->value ?> <br> REGIONAL OFFICE 1</b>
            <br>
            <br>
            <br>
            <b><?= $rd->value ?></b><br>
            Regional Director
            <br>
            <br>
            <i><u>REQUISITION OFFICE/DEPT.</u></i>
            <br>
            <br>
            <br>
            <br>
            <b><?= strtoupper($model->requesterName) ?></b><br>
            <?= $model->requester->position ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <i><u>PARTY OF THE SECOND PART</u></i>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <b><?= $supplier->business_name ?></b><br>
            (Signature)
            <br>
            <br>
            <i><u>FUNDS AVAILABLE:</u></i>
            <br>
            <br>
            <br>
            <br>
            <b><?= $regionalAccountant ? strtoupper($regionalAccountant->name) : 'Setup Regional Accountant in Signatories Table' ?></b><br>
            Regional Accountant
        </div>
    </div>
    <div class="pull-right">
    <?= Html::submitButton('<i class="fa fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
    </div>
    <div class="clearfix"></div>
</div>

<?php ActiveForm::end(); ?>
<?php
    $script = !is_null($bid) ? '
        $("#contract-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Contract has been saved");
                    createContract('.$model->id.','.$bid->id.','.$supplier->id.', '.$j.', '.$i.', '.$k.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });' : 
        '
        $("#contract-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                 url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Contract has been saved");
                    createContract('.$model->id.',"null",'.$supplier->id.', '.$j.', '.$i.', '.$k.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });';

    $script .= '
        function printPo(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-po']).'?id=" + id, 
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
    ';

    $this->registerJs($script, View::POS_END);
?>