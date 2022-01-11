<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use common\modules\v1\models\Ppmp;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\PpmpSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'PPMP';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ppmp-index">

    <p>
    	<?= Html::button('<i class="fa fa-plus"></i> Create<br>Empty', ['value' => Url::to(['/v1/ppmp/create']), 'class' => 'btn btn-app', 'id' => 'create-button', 'style' => 'padding-bottom: 60px;']) ?>
    	<?= Html::button('<i class="fa fa-copy"></i> Copy<br>Existing', ['value' => Url::to(['/v1/ppmp/copy']), 'class' => 'btn btn-app', 'id' => 'copy-button', 'style' => 'padding-bottom: 60px;']) ?>
    </p>

    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary collapsed-box">
                <div class="box-header panel-title"><i class="fa fa-search"></i> Search Filter
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <?= $this->render('_search', [
                        'model' => $searchModel,
                        'offices' => $offices,
                        'years' => $years,
                        'stages' => $stages,
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> PPMP List</div>
                <div class="box-body">
                <?= GridView::widget([
                    'tableOptions' => [
                        'class' => 'table table-hover table-responsive',
                    ],
                    'dataProvider' => $dataProvider,
                    'showFooter' => true,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        'officeName',
                        'year',
                        [
                            'header' => 'Stage',
                            'attribute' => 'stage',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'text-align: center;'],
                            'value' => function($ppmp){
                                $color = ['Indicative' => 'red', 'Adjusted' => 'green', 'Final' => 'blue'];
                                return '<span class="badge bg-'.$color[$ppmp->stage].'">'.$ppmp->stage.'</span>';
                            }
                        ],
                        'creatorName',
                        'date_created',
                        //'updated_by',
                        //'date_updated',
                        [
                            'header' => 'Total', 
                            'attribute' => 'total',
                            'contentOptions' => ['style' => 'text-align: right;'],
                            'value' => function($ppmp){
                                return number_format($ppmp->total, 2);
                            },
                            'footerOptions' => ['style' => 'text-align: right;'],
                            'value' => function($item){
                                return number_format($item->total, 2);
                            },
                            'footer' => Ppmp::pageQuantityTotal($dataProvider->models, 'total'),
                        ],
                        [
                            'header' => 'Status',
                            'attribute' => 'status.status',
                        ],
                        [
                            'format' => 'raw', 
                            'value' => function($model){
                                return Html::a('View', ['/v1/ppmp/view', 'id' => $model->id],['class' => 'btn btn-primary btn-xs btn-block']);
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
    'size' => "modal-sm",
    'header' => '<div id="create-modal-header"><h4>Create PPMP</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'copy-modal',
    'size' => "modal-sm",
    'header' => '<div id="copy-modal-header"><h4>Copy PPMP</h4></div>',
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