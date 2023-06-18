<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\TravelOrder */
?>
<h4>Dispatched Vehicles
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>
    <br>
    <span style="font-weight: normal; font-size: 14px;"><?= $model->statusInfo ?></span>
</h4>
<hr style="opacity: 0.1;">
<span class="pull-right">
    <?= Yii::$app->user->can('Dispatcher') ? $model->withVehicle === 1 ? $model->isDirector_Approved != 0 ? Html::button('<i class="fa fa-truck"></i> Dispatch Vehicle', ['value' => Url::to(['/v1/travel-order/dispatch', 'id' => $model->TO_NO]), 'class' => 'btn btn-success', 'title' => 'Dispatch Vehicle', 'id' => 'dispatch-vehicle-button']) : '' : '' : '' ?>
</span>
<div class="clearfix"></div>

<table class="table table-responsive table-hover">
    <thead>
        <tr>
            <th style="width: 18%;">Vehicle</th>
            <th style="width: 18%;">Driver</th>
            <th style="width: 18%;">Approved By</th>
            <th style="width: 18%;">Date Approved</th>
            <th style="width: 18%;">Remarks</th>
            <th style="width: 10%;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    <?php if($model->getTravelOrderVehicles()->where(['is not', 'vehicle_id', null])->all()){ ?>
        <?php foreach($model->getTravelOrderVehicles()->where(['is not', 'vehicle_id', null])->all() as $vehicle){ ?>
        <tr> 
            <td><?= $vehicle->vehicle ? $vehicle->vehicle->vehicle_description : '' ?></td>   
            <td><?= $vehicle->driver ? $vehicle->driver->driverName : '' ?></td>
            <td><?= $vehicle->approver ? $vehicle->approver->fname.' '.$vehicle->approver->lname : '' ?></td>
            <td><?= date("F j, Y H:i:s", strtotime($vehicle->date_approved)) ?></td>
            <td><?= $vehicle->remarks ?></td>
            <td align=right>
                <?= Yii::$app->user->can('Dispatcher') ? $model->withVehicle === 1 ? $model->isDirector_Approved != 0 ? Html::button('<i class="fa fa-edit"></i>', ['value' => Url::to(['/v1/travel-order/update-dispatch', 'id' => $vehicle->id]), 'class' => 'btn btn-sm btn-primary update-dispatch-vehicle-button', 'title' => 'Dispatch Vehicle']) : '' : '' : '' ?>
                <?= Yii::$app->user->can('Dispatcher') ? $model->withVehicle === 1 ? $model->isDirector_Approved != 0 ? Html::a('<i class="fa fa-trash"></i>', null, ['href' => 'javascript:void(0)', 'onClick' => 'deleteDispatchedVehicle('.$vehicle->id.')', 'class' => 'btn btn-danger btn-sm']) : '' : '' : '' ?>
            </td>   
        </tr>
        <?php } ?>
    <?php }else{ ?>
        <tr>
            <td colspan=6 align=center>No dispatched vehicles found.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<br>
<br>
<span class="pull-right">
    <?= Html::a('Close', ['/v1/travel-order'], ['class' => 'btn btn-default'])?>
</span>

<?php
  Modal::begin([
    'id' => 'dispatch-vehicle-modal',
    'size' => "modal-sm",
    'header' => '<div id="dispatch-vehicle-modal-header"><h4>Dispatch Vehicle</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="dispatch-vehicle-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-dispatch-vehicle-modal',
    'size' => "modal-sm",
    'header' => '<div id="update-dispatch-vehicle-modal-header"><h4>Edit Dispatched Vehicle</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-dispatch-vehicle-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $("#dispatch-vehicle-button").click(function(){
            $("#dispatch-vehicle-modal").modal("show").find("#dispatch-vehicle-modal-content").load($(this).attr("value"));
        });     

        $(".update-dispatch-vehicle-button").click(function(){
            $("#update-dispatch-vehicle-modal").modal("show").find("#update-dispatch-vehicle-modal-content").load($(this).attr("value"));
        });

        function deleteDispatchedVehicle(id)
        {
            var con = confirm("Are you sure you want to delete this record?");
            if(con===true)
            {
                $.ajax({
                    url: "'.Url::to(['/v1/travel-order/delete-dispatch']).'?id=" + id,
                    success: function (data) {
                        console.log(this.data);
                        alert("Dispatched vehicle deleted");
                        viewVehicleInfo('.$model->TO_NO.');
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        }
    ';

    $this->registerJs($script, View::POS_END);
?>