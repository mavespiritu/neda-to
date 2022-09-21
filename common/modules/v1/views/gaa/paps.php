<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\sortinput\SortableInput;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Ppmp */
/* @var $form yii\widgets\ActiveForm */
$objectString = json_encode($model->getAppropriationPaps()->asArray()->all());

?>

<div class="paps">

    <div class="row">
        <div class="col-md-6 col-xs-12">
            <input type="checkbox" name="checboxall" id="delete-all-paps"> Select All
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="pull-right">
                <?= Html::button("Set as default",['class' => 'btn btn-outline-secondary default-program']) ?>
                <?= Html::button("Remove selected",['class' => 'btn btn-danger delete-selected-paps']) ?>
            </div>
        </div>
    </div>

    <br>

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
        'id' => 'paps-arrangement-form',
    ]); ?>

    <?= !empty($items) ? $form->field($papModel, 'arrangement')->widget(SortableInput::classname(), [
        'sortableOptions' => [
            'pluginEvents' => [
                'sortupdate' => '
                    function(e) { 
                        e.preventDefault();
                        var form = $("#paps-arrangement-form");
                        var formData = form.serialize();
                        $.ajax({
                            url: "'.Url::to(['/v1/gaa/programs', 'id' => $model->id]).'",
                            type: "POST",
                            data: formData,
                            error: function (er) {
                                console.log(er);
                            }
                        });
                        return false;
                    }
                '
            ]
        ],
        'items' => $items,
        'hideInput' => true,
        ])->label(false) : '<p class="text-center">No programs included.</p>'
    ?>

    <br>

    <div class="pull-right">
        <?= Html::button("Set as default",['class' => 'btn btn-outline-secondary default-program']) ?>
        <?= Html::button("Remove selected",['class' => 'btn btn-danger delete-selected-paps']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
    $this->registerJs('
        $("document").ready(function(){
            $("#delete-all-paps").click(function(){
                $(".chkbox-paps:checkbox").not(this).prop("checked", this.checked);
            });

            $(".delete-selected-paps").click(function(){
                const ids = $(".chkbox-paps:checkbox:checked").map(function() {
                        return this.value;
                }).get();

                if(ids != "")
                {
                    var con = confirm("Are you sure you want to remove selected programs?");
                    if(con == true)
                    { 
                        $.ajax({
                            url: "'.Url::to(['/v1/gaa/delete-program/']).'?id="+ '.$model->id.',
                            method: "POST",
                            data: "ids="+JSON.stringify(ids),
                            success: function (data) {
                                programs();
                            },
                            error: function (err) {
                                console.log(err);
                            }
                        });
                    }else{

                    }
                }else{
                    alert("Please check at least one program");
                }
            });

            $(".default-program").click(function(){
                var arrangement = $("#appropriationpap-arrangement").val();
                if(arrangement != "")
                {
                    var con = confirm("Are you sure you want to set these programs for future GAA preparations?");
                    if(con == true)
                    { 
                        var form = $("#paps-arrangement-form");
                         $.ajax({
                            url: "'.Url::to(['/v1/gaa/default-program/']).'",
                            method: "POST",
                            data: "models=" + JSON.stringify('.$objectString.'),
                            success: function (data) {
                                alert("Programs are set as default successfully");
                            },
                            error: function (err) {
                                console.log(err);
                            }
                        }); 
                    }
                }else{
                    alert("No programs selected");
                }
            });
        });
    ');
?>
