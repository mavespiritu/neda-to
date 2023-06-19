<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\TravelOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Travel Orders';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="travel-order-index">

    <h2><?= $this->title ?>
    <span class="pull-right">
        <?= Html::button('<i class="fa fa-search"></i> Search Travel Orders', ['value' => Url::to(['/v1/travel-order/search']), 'class' => 'btn btn-default', 'title' => 'Search Travel Orders', 'id' => 'search-button']) ?>
        <?= Html::a('<i class="fa fa-refresh"></i> Refresh', ['index'], ['class' => 'btn btn-default']) ?>
        <?= Html::a('<i class="fa fa-plus"></i> Add New', ['create'], ['class' => 'btn btn-primary']) ?>
    </span>
    </h2>
    <br>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'options' => ['class' => 'gridview'],
        'tableOptions' => ['class' => 'table table-hover table-striped table-responsive'],
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'TO_NO',
                'format' => 'raw',
                'value' => function($model){
                    return '<b>'.Html::a($model->TO_NO, ['/v1/travel-order/view', 'id' => $model->TO_NO]).'</b>';
                }
            ],
            [
                'attribute' => 'TO_subject',
                'header' => 'Purpose',
                'format' => 'raw',
                'contentOptions' => ['style' => 'width: 20%;'],
                'value' => function($model){
                    return $model->TO_subject;
                }
            ],
            'travelTypeName',
            [
                'attribute' => 'date_from',
                'header' => 'Start Date',
                'format' => 'raw',
                'value' => function($model){
                    return date("F j, Y", strtotime($model->date_from));
                }
            ],
            [
                'attribute' => 'date_to',
                'header' => 'End Date',
                'format' => 'raw',
                'value' => function($model){
                    return date("F j, Y", strtotime($model->date_to));
                }
            ],
            'creatorName',
            [
                'attribute' => 'date_filed',
                'format' => 'raw',
                'value' => function($model){
                    return date("F j, Y H:i:s", strtotime($model->date_filed));
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model){
                    return $model->status;
                }
            ],
            [
                'format' => 'raw', 
                'contentOptions' => ['style' => 'width: 1%'],
                'value' => function($model){
                    $content = Yii::$app->user->can('Staff') ? Yii::$app->user->identity->userinfo->EMP_N == $model->TO_creator ? $model->isDirector_Approved != 1 ? Html::a('<i class="fa fa-pencil"></i>', ['/v1/travel-order/update', 'id' => $model->TO_NO],['class' => 'btn btn-info btn-xs']) : '' : '' : '';

                    return $content;
            }],
            [
                'format' => 'raw', 
                'contentOptions' => ['style' => 'width: 1%; padding-left: 0;'],
                'value' => function($model){
                    $content = Yii::$app->user->can('Staff') ? Yii::$app->user->identity->userinfo->EMP_N == $model->TO_creator ? $model->isDirector_Approved != 1 ? Html::a('<i class="fa fa-trash"></i>', ['delete', 'id' => $model->TO_NO], [
                        'class' => 'btn btn-danger btn-xs',
                        'data' => [
                            'confirm' => 'Are you sure you want to delete this record?',
                            'method' => 'post',
                        ],
                    ]) : '' : '' : '';

                    return $content;
            }],
        ],
    ]); ?>


</div>
<?php
Modal::begin([
  'id' => 'search-modal',
  'size' => "modal-sm",
  'header' => '<div id="search-modal-header"><h4>Search Travel Orders</h4></div>',
  'options' => ['tabindex' => false],
]);
echo '<div id="search-modal-content"></div>';
Modal::end();
?>
<?php
    $script = '
        $("#search-button").click(function(){
            $("#search-modal").modal("show").find("#search-modal-content").load($(this).attr("value"));
        });  
    ';

    $this->registerJs($script, View::POS_END);
?>