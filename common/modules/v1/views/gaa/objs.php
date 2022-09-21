<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\sortinput\SortableInput;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Ppmp */
/* @var $form yii\widgets\ActiveForm */
$objectString = json_encode($model->getAppropriationObjs()->asArray()->all());
?>
<?php $objectsUrl = \yii\helpers\Url::to(['/v1/gaa/object-list']); ?>

<div class="objs">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
        'id' => 'objs-arrangement-form',
    ]); ?>

    <div class="row">
        <div class="col-md-6 col-xs-12">
            <input type="checkbox" name="checboxall" id="delete-all-objs"> Select All
        </div>
        <div class="col-md-6 col-xs-12">
            <div class="pull-right">
                <?= Html::button("Set as default",['class' => 'btn btn-outline-secondary default-object']) ?>
                <?= Html::button("Remove selected",['class' => 'btn btn-danger delete-selected-objs']) ?>
            </div>
        </div>
    </div>

    <br>

    <?= !empty($items) ? $form->field($objModel, 'arrangement')->widget(SortableInput::classname(), [
        'sortableOptions' => [
            'pluginEvents' => [
                'sortupdate' => '
                    function(e) { 
                        e.preventDefault();
                        var form = $("#objs-arrangement-form");
                        var formData = form.serialize();
                        $.ajax({
                            url: "'.Url::to(['/v1/gaa/objects', 'id' => $model->id]).'",
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
        ])->label(false) : '<p class="text-center">No objects included.</p>'
    ?>

    <br>
    
    <div class="pull-right">
        <?= Html::button("Set as default",['class' => 'btn btn-outline-secondary default-object']) ?>
        <?= Html::button("Remove selected",['class' => 'btn btn-danger delete-selected-objs']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
    $this->registerJs('
        $("document").ready(function(){
            $("#delete-all-objs").click(function(){
                $(".chkbox-objs:checkbox").not(this).prop("checked", this.checked);
            });

            $(".delete-selected-objs").click(function(){
                const ids = $(".chkbox-objs:checkbox:checked").map(function() {
                        return this.value;
                }).get();

                if(ids != "")
                {
                    var con = confirm("Are you sure you want to remove selected objects?");
                    if(con == true)
                    { 
                        $.ajax({
                            url: "'.Url::to(['/v1/gaa/delete-object/']).'?id="+ '.$model->id.',
                            method: "POST",
                            data: "ids="+JSON.stringify(ids),
                            success: function (data) {
                                objectForm();
                                objects();
                            },
                            error: function (err) {
                                console.log(err);
                            }
                        });
                    }else{

                    }
                }else{
                    alert("Please check at least one object");
                }
            });

            $(".default-object").click(function(){
                var arrangement = $("#appropriationobj-arrangement").val();
                if(arrangement != "")
                {
                    var con = confirm("Are you sure you want to set these objects for future GAA preparations?");
                    if(con == true)
                    { 
                        var form = $("#objs-arrangement-form");
                         $.ajax({
                            url: "'.Url::to(['/v1/gaa/default-object/']).'",
                            method: "POST",
                            data: "models=" + JSON.stringify('.$objectString.'),
                            success: function (data) {
                                alert("Objects are set as default successfully");
                            },
                            error: function (err) {
                                console.log(err);
                            }
                        }); 
                    }
                }else{
                    alert("No objects selected");
                }
            });
        });
    ');
?>
