<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\SupplierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Suppliers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-index">

    <p>
        <?= Yii::$app->user->can('ProcurementStaff') ? Html::a('<i class="fa fa-plus"></i> Create', ['create'], ['class' => 'btn btn-app']) : '' ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'options' => [
            'class' => 'table-responsive',
        ],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => Yii::$app->user->can('ProcurementStaff') ? [
            ['class' => 'yii\grid\SerialColumn'],

            'business_name',
            'business_address:ntext',
            'owner_name',
            'contact_person',
            'landline',
            'mobile_no',
            'email_address:email',
            'philgeps_no',
            'bir_registration',
            'tin_no',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{update}{delete}']
            ] :
            [
            ['class' => 'yii\grid\SerialColumn'],

            'business_name',
            'business_address:ntext',
            'owner_name',
            'contact_person',
            'landline',
            'mobile_no',
            'email_address:email',
            'philgeps_no',
            'bir_registration',
            'tin_no',
            ],
    ]); ?>


</div>
