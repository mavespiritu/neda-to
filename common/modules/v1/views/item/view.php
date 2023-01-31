<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\Item */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="item-view">
    <?= $this->render('_menu', ['model' => $model]) ?>
    <div class="row">
        <div class="col-md-8 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i>Object Assignment</div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4 col-xs-12">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                <p class="panel-title">Attach Item to Object of Expense</p><br>
                                    <?= $this->render('_object-form',[
                                        'objectItemModel' => $objectItemModel,
                                        'objs' => $objs
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-xs-12">
                        <p class="panel-title">Assigned Objects</p><br>
                        <?= GridView::widget([
                            'options' => [
                                'class' => 'table-responsive',
                            ],
                            'dataProvider' => $objectItemdataProvider,
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                [
                                    'header' => 'Object',
                                    'attribute' => 'obj.objectTitle'
                                ],
                                [
                                    'format' => 'raw', 
                                    'value' => function($objectItemSearchModel){
                                        return Html::a('Delete', ['/v1/item/delete-object', 'id' => $objectItemSearchModel->id],['class' => 'btn btn-danger btn-xs btn-block',
                                        'data' => [
                                            'confirm' => 'This will unassign item to the object. Would you like to proceed?',
                                            'method' => 'post',
                                        ],]);
                                }],
                            ],
                        ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i>Item Details</div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'header' => 'Mode of Procurement',
                                'attribute' => 'procurement_mode_id',
                                'value' => function($model){ return $model->procurementMode->title; }
                            ],
                            'category',
                            'code',
                            'title:ntext',
                            'unit_of_measure',
                            [
                                'header' => 'Cost Per Unit',
                                'attribute' => 'cost_per_unit',
                                'value' => function($model){ return number_format($model->cost_per_unit, 2); }
                            ],
                            'cse',
                            'classification',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i>Cost History</div>
                <div class="box-body">
                    <?= GridView::widget([
                        'options' => [
                            'class' => 'table-responsive table-condensed',
                        ],
                        'dataProvider' => $itemCostdataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'header' => 'Cost Per Unit',
                                'attribute' => 'cost',
                                'value' => function($costModel){ return number_format($costModel->cost, 2); }
                            ],
                            'datetime',
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
