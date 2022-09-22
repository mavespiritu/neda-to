<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use common\modules\v1\models\Ris;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\RisSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'RIS';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ris-index">

    <p>
        <?= Html::button('<i class="fa fa-plus"></i> Create', ['value' => Url::to(['/v1/ris/create']), 'class' => 'btn btn-app', 'id' => 'create-button']) ?>
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
                        'offices' => $offices,
                        'fundSources' => $fundSources,
                        'types' => $types
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> RIS List</div>
                <div class="box-body">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'options' => ['class' => 'table table-hover table-responsive gridview'],
                        'showFooter' => true,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            [
                                'attribute' => 'ris_no',
                                'format' => 'raw',
                                'value' => function($model){
                                    return '<b>'.Html::a($model->ris_no, ['/v1/ris/info', 'id' => $model->id]).'</b>';
                                }
                            ],
                            [
                                'header' => 'Status',
                                'attribute' => 'statusName',
                                'format' => 'raw',
                                'value' => function($ris){
                                    $color = ['For Revision' => 'orange', 'Disapproved' => 'red', 'Approved' => 'green', 'Draft' => 'blue', 'No status' => 'white'];
                                    return '<span class="badge bg-'.$color[$ris->statusName].'">'.$ris->statusName.'</span>';
                                }
                            ],
                            [
                                'attribute' => 'type',
                                'format' => 'raw',
                                'value' => function($model){
                                    return $model->type == 'Supply' ? 'Goods' : 'S/C';
                                }
                            ],
                            'officeName',
                            [
                                'attribute' => 'purpose',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'width: 15%;'],
                                'value' => function($model){
                                    return $model->purpose;
                                }
                            ],
                            'fundSourceName',
                            'creatorName',
                            'requesterName',
                            'date_required',
                            [
                                'header' => 'Included PREXC',
                                'attribute' => 'prexc',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'font-size: 10px;'],
                                'value' => function($model){
                                    return $model->prexcs;
                                }
                            ],
                            [
                                'header' => 'Realigned PREXC',
                                'attribute' => 'realignedPrexc',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'font-size: 10px;'],
                                'value' => function($model){
                                    return $model->realignedPrexcs;
                                }
                            ],
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
                                'footer' => Ris::pageQuantityTotal($dataProvider->models, 'total'),
                            ],
                            [
                                'format' => 'raw', 
                                'value' => function($model){
                                    return Html::a('View', ['/v1/ris/info', 'id' => $model->id],['class' => 'btn btn-primary btn-xs btn-block']);
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
    'header' => '<div id="create-modal-header"><h4>Create RIS</h4></div>',
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