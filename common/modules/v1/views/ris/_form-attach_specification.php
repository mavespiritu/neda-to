<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>

<div class="specification-form">

    <?php $form = ActiveForm::begin(['id' => 'attach-specification', 'options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= \file\components\AttachmentsInput::widget([
	'id' => 'file-input', // Optional
	'model' => $spec,
	'options' => [ // Options of the Kartik's FileInput widget
		'multiple' => true, // If you want to allow multiple upload, default to false
	],
	'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget 
		'maxFileCount' => 10, // Client max files
        'showPreview' => false,
        'showCaption' => true,
        'showRemove' => true,
        'showUpload' => false
	]
]) ?>
  
    <div class="form-group pull-right">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    
    <div class="clearfix"></div>

    <?php ActiveForm::end(); ?>

</div>