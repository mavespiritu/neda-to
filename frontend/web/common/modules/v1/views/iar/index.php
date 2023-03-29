<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\IarSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Iars';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="iar-index">

    <p>
        <?= Html::a('<i class=\"fa fa-plus\"></i> Create', ['create'], ['class' => 'btn btn-app']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'options' => [
            'class' => 'table-responsive',
        ],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'pr_id',
            'po_id',
            'iar_no',
            'iar_date',
            //'invoice_no',
            //'invoice_date',
            //'inspected_by',
            //'date_inspected',
            //'received_by',
            //'date_received',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{update}{delete}'],
        ],
    ]); ?>


</div>
