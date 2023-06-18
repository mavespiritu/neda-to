<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\TravelOrder */
?>
<h4>Travel Information
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>
    <br>
    <span style="font-weight: normal; font-size: 14px;"><?= $model->statusInfo ?></span>
</h4>
<br>
<?= DetailView::widget([
    'model' => $model,
    'options' => [
        'class' => 'table table-responsive table-hover',
    ],
    'attributes' => [
        'TO_NO',
        [
            'attribute' => 'TO_subject',
            'label' => 'Purpose',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width: 60%; line-height: 30px;'],
            'value' => function($model){
                return $model->TO_subject;
            }
        ],
        [
            'attribute' => 'date_from',
            'label' => 'Inclusive Date',
            'format' => 'raw',
            'value' => function($model){
                return $model->date_from === $model->date_to ? date("F j, Y", strtotime($model->date_from)) : date("F j, Y", strtotime($model->date_from)).' - '.date("F j, Y", strtotime($model->date_to));
            }
        ],
        [
            'attribute' => 'staffs',
            'label' => 'Included Staff',
            'format' => 'raw',
            'value' => function($model){
                $content = '<ul style="padding: 0; line-height: 25px;">';

                if($model->concernStaffs){
                    foreach($model->concernStaffs as $staff){
                        $content .= '<li>'.$staff->name.' ('.$staff->employee->division_id.')</li>';
                    }
                }

                $content .= '</ul>';

                return $content;
            }
        ],
        [
            'attribute' => 'type_of_travel',
            'label' => 'Travel Type',
            'format' => 'raw',
            'value' => function($model){
                return $model->travelTypeName;
            }
        ],
        [
            'attribute' => 'withVehicle',
            'label' => 'Request with vehicle?',
            'format' => 'raw',
            'value' => function($model){
                return $model->withVehicle === 1 ? 'Yes' : 'No';
            }
        ],
        [
            'attribute' => 'TO_creator',
            'label' => 'Created By',
            'format' => 'raw',
            'value' => function($model){
                return $model->creatorName;
            }
        ],
        [
            'attribute' => 'date_filed',
            'format' => 'raw',
            'value' => function($model){
                return date("F j, Y H:i:s", strtotime($model->date_filed));
            }
        ],
        'otherpassenger:ntext',
        'othervehicle:ntext',
        'otherdriver:ntext',
    ],
]) ?>
<br>
<br>
<span class="pull-right">
    <?= Html::a('Close', ['/v1/travel-order'], ['class' => 'btn btn-default'])?>
</span>