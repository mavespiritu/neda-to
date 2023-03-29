<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\IarSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'IAR';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="iar-index">

    <p>
        <?= Html::a('<i class="fa fa-plus"></i> Create', ['/v1/iar/create'], ['class' => 'btn btn-app', 'id' => 'create-button']) ?>
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
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> IAR List</div>
                <div class="box-body">
                    <?= GridView::widget([
                        'options' => [
                            'class' => 'table-responsive',
                        ],
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            'id',
                            'iar_no',
                            'iar_date',
                            'pr_id',
                            'po_id',
                            'invoice_no',
                            'invoice_date',
                            'inspectorName',
                            'date_inspected',
                            //'receiverName',
                            //'received_by',
                            'status',

                            ['class' => 'yii\grid\ActionColumn', 'template' => '{update}{delete}'],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
