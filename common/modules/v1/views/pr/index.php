<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use common\modules\v1\models\Pr;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\PrSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'PR';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pr-index">

    <p>
        <?= Html::button('<i class="fa fa-plus"></i> Create', ['value' => Url::to(['/v1/pr/create']), 'class' => 'btn btn-app', 'id' => 'create-button']) ?>
    </p>

    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-search"></i> Search Filter
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <?= $this->render('_search', [
                        'model' => $searchModel,
                        'types' => $types,
                        'fundSources' => $fundSources,
                        'offices' => $offices,
                        'procurementModes' => $procurementModes,
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> PR List</div>
                <div class="box-body">
                    <?= GridView::widget([
                        'options' => ['class' => 'gridview'],
                        'tableOptions' => ['class' => 'table table-hover table-bordered table-condensed table-striped table-responsive'],
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            [
                                'attribute' => 'pr_no',
                                'format' => 'raw',
                                'value' => function($model){
                                    return '<b>'.Html::a($model->pr_no, ['/v1/pr/view', 'id' => $model->id]).'</b>';
                                }
                            ],
                            [
                                'header' => 'RIS',
                                'attribute' => 'risNos',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'width: 7%; font-size: 10px;'],
                                'value' => function($model){
                                    return $model->risNos;
                                }
                            ],
                            [
                                'header' => 'Status',
                                'attribute' => 'statusName',
                                'format' => 'raw',
                                'value' => function($pr){
                                    $color = ['For Approval' => 'teal', 'For Revision' => 'orange', 'Disapproved' => 'red', 'Approved' => 'green', 'Draft' => 'blue', 'No status' => 'white'];
                                    return '<span class="badge bg-'.$color[$pr->statusName].'">'.$pr->statusName.'</span>';
                                }
                            ],
                            [
                                'attribute' => 'type',
                                'format' => 'raw',
                                'value' => function($model){
                                    return $model->type == 'Supply' ? 'Goods' : 'Service/Contract';
                                }
                            ],
                            'officeName',
                            'procurementModeName',
                            [
                                'attribute' => 'purpose',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'width: 20%;'],
                                'value' => function($model){
                                    return $model->purpose;
                                }
                            ],
                            'fundSourceName',
                            'creatorName',
                            'requesterName',
                            'date_requested',
                            [
                                'header' => 'Total', 
                                'attribute' => 'total',
                                'headerOptions' => ['style' => 'text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right;'],
                                'value' => function($model){
                                    return number_format($model->total, 2);
                                },
                                'footerOptions' => ['style' => 'text-align: right;'],
                                'value' => function($model){
                                    return number_format($model->total, 2);
                                },
                                'footer' => Pr::pageQuantityTotal($dataProvider->models, 'total'),
                            ],
                            [
                                'format' => 'raw', 
                                'value' => function($model){
                                    return Html::a('View', ['/v1/pr/view', 'id' => $model->id],['class' => 'btn btn-primary btn-xs btn-block']);
                            }],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
  Modal::begin([
    'id' => 'create-modal',
    'size' => "modal-md",
    'header' => '<div id="create-modal-header"><h4>Create PR</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(document).ready(function(){
            $("#create-button").click(function(){
              $("#create-modal").modal("show").find("#create-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>