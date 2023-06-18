<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\TravelOrder */
?>
<h4>Destinations
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>
    <br>
    <span style="font-weight: normal; font-size: 14px;"><?= $model->statusInfo ?></span>
</h4>
<hr style="opacity: 0.1;">
<table class="table table-responsive table-hover">
    <thead>
        <tr>
            <th style="width: 25%;">Region</th>
            <th style="width: 25%;">Province</th>
            <th style="width: 25%;">City/Municipality</th>
            <th style="width: 25%;">Specific Location</th>
        </tr>
    </thead>
    <tbody>
    <?php if($model->travelOrderLocations){ ?>
        <?php foreach($model->travelOrderLocations as $location){ ?>
        <tr>
            <td><?= $location->location->citymun->province->regionTitle->description ?></td>   
            <td><?= $location->location->citymun->province->description ?></td>   
            <td><?= $location->location->citymun->description ?></td>   
            <td><?= $location->location->description ?></td>   
        </tr>
        <?php } ?>
    <?php }else{ ?>
        <tr>
            <td colspan=4 align=center>No destinations found.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<br>
<br>
<span class="pull-right">
    <?= Html::a('Close', ['/v1/travel-order'], ['class' => 'btn btn-default'])?>
</span>