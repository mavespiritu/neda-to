<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use frontend\assets\AppAsset;

$asset = AppAsset::register($this);
?>

<?php $form = ActiveForm::begin([
    'id' => 'ors-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>
<div class="ors-content">
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($orsModel, 'ors_no')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($orsModel, 'ors_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]); ?>
        </div>
    </div>
    <p><i class="fa fa-exclamation-circle"></i> Select items to obligate.</p>
    <table class="table table-bordered table-striped table-hover table-condensed table-responsive">
        <thead>
            <tr>
                <td align=center><b>Stock/Property No.</b></td>
                <td align=center><b>Unit</b></td>
                <td align=center><b>Description</b></td>
                <td align=center><b>Qty</b></td>
                <td align=center><b>Unit Cost</b></td>
                <td align=center><b>Amount</b></td>
                <td>&nbsp;</td>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($items)){ ?>
            <?php foreach($items as $item){ ?>
                
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
    <?= $form->field($orsModel, 'responsibility_center')->textInput(['maxlength' => true]) ?>
    <br>
    <div class="pull-right">
    <?= Html::submitButton('<i class="fa fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
    </div>
    <div class="clearfix"></div>
</div>

<?php ActiveForm::end(); ?>
<?php
    $script = '
        $("#ors-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    form.enableSubmitButtons();
                    alert("Obligation has been saved successfully");
                    $(".modal").remove();
                    $(".modal-backdrop").remove();
                    $("body").removeClass("modal-open");
                    menu('.$model->id.');
                    obligatePo('.$model->id.','.$po->id.','.$i.');
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