<?php 
    use yii\helpers\Html;
?>

<span class="pull-right">
    <ul style="list-style: none;">
        <li style="display: inline-block; margin-right: 10px;"><a href="#" class="lead text-black" title='Print Travel Order' onclick="printTravelOrder()"><i class="glyphicon glyphicon-print"></i></a></li>
        <?php if(Yii::$app->user->can('Staff') && Yii::$app->user->identity->userinfo->EMP_N == $model->TO_creator &&$model->isDirector_Approved != 1){ ?>
            <li style="display: inline-block; margin-right: 10px;"><?= Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['update', 'id' => $model->TO_NO],['class' => 'lead text-black', 'title' => 'Update Travel Order']) ?>
            <li style="display: inline-block; margin-right: 10px;"><?= Html::a('<i class="glyphicon glyphicon-trash"></i>', ['delete', 'id' => $model->TO_NO,
            ], [
                'class' => 'lead text-black',
                'title' => 'Delete Travel Order',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?></li>
        <?php } ?>
    </ul>
</span>