<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use yii\widgets\MaskedInput;
use faryshta\disableSubmitButtons\Asset as DisableButtonAsset;
DisableButtonAsset::register($this);
use yii\web\View;
use yii\bootstrap\Modal;
/* @var $model common\modules\v1\models\Pr */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $form = ActiveForm::begin([
    'id' => 'approve-form',
    'options' => ['class' => 'disable-submit-buttons'],
]); ?>

<p>
    <b>Approver:</b> <br>
    <?= $approverName ?> <br><br>
    <b>Date Approved:</b> <br>
    <?= date("F j, Y H:i:s") ?>
</p>


<?= $form->field($approvalModel, 'remarks')->textarea(['rows' => 3]) ?>

<div class="form-group pull-right"> 
    <?= Html::submitButton('Approve', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait'], 'data' => [
        'method' => 'post',
    ]]) ?>
</div>
<div class="clearfix"></div>

<?php ActiveForm::end(); ?>

<?php
    $script = '
    $("#approve-form").on("beforeSubmit", function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                form.enableSubmitButtons();
                alert("Travel order is now approved.");
                $(".modal").remove();
                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
                viewVehicleInfo('.$model->TO_NO.');
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