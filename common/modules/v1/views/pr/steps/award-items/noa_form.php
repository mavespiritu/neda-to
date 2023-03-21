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

<h3 class="panel-title"><?= $j ?>.<?= $i ?>.<?= $k ?> NOA for <?= $supplier->business_name ?>
<span class="pull-right">
<?= !$noaModel->isNewRecord ? Html::a('<i class="fa fa-print"></i> Print', null, ['href' => 'javascript:void(0)', 'onClick' => 'printNoa('.$noaModel->id.')', 'class' => 'btn btn-info']) : '' ?>
</span>
</h3>
<br>
<?php $form = ActiveForm::begin([
    'id' => 'noa-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<div class="noa-content">
    <h5 class="text-center"><b>NOTICE OF AWARD</b></h5>
    <br>
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <?= $form->field($noaModel, 'date_created')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date', 'autocomplete' => 'off'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]); ?>
        </div>
    </div>
    <br>
    <b><?= $supplier->business_name ?></b>
    <br>
    <?= $supplier->business_address ?>
    <br>
    <br>
    <b>Dear Ma'am/Sir:</b>
    <br>
    <br>
    <p>We are pleased to notify you that the <b>"<?= $model->purpose ?>"</b> is hereby awarded to you as the bidder with the Lowest Responsive Bid at a Contract Price equivalent to <b><?= strtoupper(Yii::$app->controller->module->getNumberToWords((sprintf('%0.2f',$bid->getBidTotal($supplier->id))))) ?> (Php <?= number_format($bid->getBidTotal($supplier->id), 2) ?>).</b></p>
    <br>
    <br>
    <table class="table table-bordered table-striped table-responsive table-condensed">
        <thead>
            <tr>
                <td align=center><b>Unit</b></td>
                <td align=center><b>Description</b></td>
                <td align=center><b>Qty</b></td>
                <td align=right><b>Unit Cost</b></td>
                <td align=right><b>Amount</b></td>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($items)){ ?>
                <?php foreach($items as $item){ ?>
                    <tr>
                        <td align=center><?= $item['unit'] ?></td>
                        <td><?= $item['item'] ?></td>
                        <td align=center><?= number_format($item['total'], 0) ?></td>
                        <td align=right><?= number_format($item['cost'], 2) ?></td>
                        <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <tr>
                <td align=right colspan=4><b>TOTAL AMOUNT</b></td>
                <td align=right><b>Php <?= number_format($bid->getBidTotal($supplier->id), 2) ?></b></td>
            </tr>
        </tbody>
    </table>
    <br>
    <br>
    You are, therefore, requested to enter into a contract with us upon receipt of this notice.
    <br>
    <br>
    <br>
    Very Truly Yours,
    <br>
    <br>
    <br>
    <b><?= $rd->value ?></b>
    <br>
    Regional Director
    <br>
    <br>
    <br>
    Conforme:
    <br>
    <br>
    <br>
    <span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span>
    <br>
    (Name and Signature of Bidder/Authorized Representative)
    <br>
    <br>
    <br>
    <span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span>
    <br>
    (Name of Bidder or Supplier)
    <br>
    <br>
    <br>
    <span style="display: inline-block; border-bottom: 1px solid black; width: 400px;"></span>
    <br>
    (Date) 
    </p>
</div>
<br>
<br>
<div class="pull-right">
    <?= Html::submitButton('<i class="fa fa-save"></i> Save', ['class' => 'btn btn-success']) ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>
<?php
    $script = '
        $("#noa-form").on("beforeSubmit", function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();

            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    alert("NOA has been saved");
                    createNoa('.$model->id.','.$bid->id.','.$supplier->id.','.$j.','.$i.','.$k.');
                    $("html").animate({ scrollTop: 0 }, "slow");
                },
                error: function (err) {
                    console.log(err);
                }
            }); 
            
            return false;
        });

        function printNoa(id)
        {
            var printWindow = window.open(
            "'.Url::to(['/v1/pr/print-noa']).'?id="+ id, 
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