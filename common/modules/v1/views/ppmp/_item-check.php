<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
/* @var $model common\modules\v1\models\PpmpSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ppmp-item-search">

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="row">
      <div class="col-md-3 col-xs-12">
          <?php 
              $subActivitiesUrl = \yii\helpers\Url::to(['/v1/ris/sub-activity-list']);
              echo $form->field($searchModel, 'activity_id')->widget(Select2::classname(), [
              'data' => $activities,
              'options' => ['placeholder' => 'Select Activity','multiple' => false, 'class'=>'activity-select'],
              'pluginOptions' => [
                  'allowClear' =>  true,
              ],
              'pluginEvents'=>[
                  'select2:select'=>'
                      function(){
                          $.ajax({
                              url: "'.$subActivitiesUrl.'",
                              data: {
                                      id: this.value
                                  }
                              
                          }).done(function(result) {
                              $(".sub-activity-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select PPA", allowClear: true});
                              $(".sub-activity-select").select2("val","");
                          });
                      }'

              ]
              ]);
          ?>
      </div>
      <div class="col-md-3 col-xs-12">
          <?= $form->field($searchModel, 'sub_activity_id')->widget(Select2::classname(), [
                  'data' => $subActivities,
                  'options' => ['placeholder' => 'Select PPA', 'multiple' => false, 'class' => 'sub-activity-select'],
                  'pluginOptions' => [
                      'allowClear' =>  true,
                  ],
                  
              ]);
          ?>
      </div>
      <div class="col-md-3 col-xs-12">
        <?= $form->field($searchModel, 'obj_id')->widget(Select2::classname(), [
                'data' => $objects,
                'options' => ['placeholder' => 'Select Object', 'multiple' => false, 'class' => 'obj-select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label('Object');
        ?>
      </div>
      <div class="col-md-3 col-xs-12">
        <?= $form->field($searchModel, 'type')->widget(Select2::classname(), [
                'data' => ['Original' => 'Original', 'Supplemental' => 'Supplemental'],
                'options' => ['placeholder' => 'Select Type', 'multiple' => false, 'class' => 'type-select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label('Type');
        ?>
      </div>
  </div>
  <div class="row">
    <div class="col-md-6 col-xs-12">
        <?= $form->field($searchModel, 'item_id')->widget(Select2::classname(), [
                'data' => $items,
                'options' => ['placeholder' => 'Select Object', 'multiple' => false, 'class' => 'item-select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label('Item');
        ?>
    </div>
    <div class="col-md-3 col-xs-12">
        <?= $form->field($searchModel, 'fund_source_id')->widget(Select2::classname(), [
                'data' => $fundSources,
                'options' => ['placeholder' => 'Select Object', 'multiple' => false, 'class' => 'fund-source-select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label('Fund Source');
        ?>
    </div>
  </div>
        
  <div class="form-group pull-right">
      <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
      <?= Html::resetButton('Clear', ['class' => 'btn btn-outline-secondary', 'onClick' => 'redirectPage()']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
<?php
$script = '
    function redirectPage()
    {
        window.location.href = "'.Url::to(['/v1/ppmp/item-check', 'id' => $model->id]).'";
    }
';
$this->registerJs($script, View::POS_END);
?>