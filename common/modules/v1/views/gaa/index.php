<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use common\modules\v1\models\Appropriation;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\PpmpSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'GAA';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="nep-index">

    <p>
    	<?= Html::button('<i class="fa fa-plus"></i> Create<br>Empty', ['value' => Url::to(['/v1/gaa/create']), 'class' => 'btn btn-app', 'id' => 'create-button', 'style' => 'padding-bottom: 60px;']) ?>
    	<?= Html::button('<i class="fa fa-copy"></i> Copy<br>Existing', ['value' => Url::to(['/v1/gaa/copy']), 'class' => 'btn btn-app', 'id' => 'copy-button', 'style' => 'padding-bottom: 60px;']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'options' => [
            'class' => 'table-responsive',
        ],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'showFooter' => true,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'year',
            'creatorName',
            'date_created',
            [
              'header' => 'Total', 
              'attribute' => 'total',
              'contentOptions' => ['style' => 'text-align: right;'],
              'value' => function($gaa){
                  return number_format($gaa->total, 2);
              },
              'footerOptions' => ['style' => 'text-align: right;'],
              'value' => function($gaa){
                  return number_format($gaa->total, 2);
              },
              'footer' => Appropriation::pageQuantityTotal($dataProvider->models, 'total'),
            ],
            [
                'format' => 'raw', 
                'value' => function($model){
                    return Html::a('View', ['/v1/gaa/view', 'id' => $model->id],['class' => 'btn btn-primary btn-block']);
            }],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<?php
  Modal::begin([
    'id' => 'create-modal',
    'size' => "modal-sm",
    'header' => '<div id="create-modal-header"><h4>Create GAA</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'copy-modal',
    'size' => "modal-sm",
    'header' => '<div id="copy-modal-header"><h4>Copy GAA</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="copy-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(document).ready(function(){
            $("#create-button").click(function(){
              $("#create-modal").modal("show").find("#create-modal-content").load($(this).attr("value"));
            });
            $("#copy-button").click(function(){
                $("#copy-modal").modal("show").find("#copy-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>