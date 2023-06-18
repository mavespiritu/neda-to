<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\TravelOrder */

$this->title = 'Add New Travel Order';
$this->params['breadcrumbs'][] = ['label' => 'Travel Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="travel-order-create">

    <?= $this->render('_form', [
        'model' => $model,
        'travelTypes' => $travelTypes,
        'destinationModels' => $destinationModels,
        'regions' => $regions,
        'provinces' => $provinces,
        'citymuns' => $citymuns,
        'staffs' => $staffs,
    ]) ?>

</div>
