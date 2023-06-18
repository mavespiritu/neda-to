<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\View;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\TravelOrder */

$this->title = "View Travel Order";
$this->params['breadcrumbs'][] = ['label' => 'Travel Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="travel-order-view">

    <h2><?= "Travel Order No. ".$model->TO_NO ?>
    <span class="pull-right">
        <?= Yii::$app->user->can('Approver') ? $model->isDirector_Approved != 1 ? Html::button('<i class="glyphicon glyphicon-thumbs-up"></i> Approve', ['value' => Url::to(['/v1/travel-order/approve', 'id' => $model->TO_NO]), 'class' => 'btn btn-success', 'title' => 'Approve Travel Order', 'id' => 'approve-button']) : '' : '' ?>
        <?= Yii::$app->user->can('Approver') ? $model->isDirector_Approved != 0 ? Html::button('<i class="glyphicon glyphicon-thumbs-down"></i> Disapprove', ['value' => Url::to(['/v1/travel-order/disapprove', 'id' => $model->TO_NO]), 'class' => 'btn btn-danger', 'title' => 'Disapprove Travel Order', 'id' => 'disapprove-button']) : '' : '' ?>
        <?= Yii::$app->user->can('Approver') ? $model->isDirector_Approved != '' ? Html::button('<i class="glyphicon glyphicon-pencil"></i> Open for editing', ['value' => Url::to(['/v1/travel-order/for-revision', 'id' => $model->TO_NO]), 'class' => 'btn btn-info', 'title' => 'Disapprove Travel Order', 'id' => 'for-revision-button']) : '' : '' ?>
        <?= Html::a('<i class="fa fa-plus"></i> Add New', ['create'], ['class' => 'btn btn-primary']) ?>
    </span>
    </h2>
    <?php if($model->isDirector_Approved == '' && $model->remarks != ''){ ?>
        <br>
        <p class="text-red"><i class="fa fa-exclamation-circle"></i> This travel order needs editing. Reason to edit: <b><?= $model->remarks ?></b>
        </p>
    <?php } ?>
    <br>
    <br>
    <div class="row">
        <div class="col-md-2 col-xs-12">
            <ul type="none" class="to-menu">
                <li class="travel-menu" onclick="viewTravelInfo(<?= $model->TO_NO ?>)">Travel Information</li>
                <li class="destination-menu" onclick="viewDestinationInfo(<?= $model->TO_NO ?>)">Destinations (<?= count($model->travelOrderLocations) ?>)</li>
                <?php if($model->withVehicle === 1){ ?>
                <li class="vehicle-menu" onclick="viewVehicleInfo(<?= $model->TO_NO ?>)">Dispatched Vehicles (<?= count($model->getTravelOrderVehicles()->where(['is not', 'vehicle_id', null])->all()) ?>)</li>
                <?php } ?>
                <li class="approval-menu" onclick="viewApprovalInfo(<?= $model->TO_NO ?>)">Approval Information</li>
            </ul>
        </div>
        <div class="col-md-10 col-xs-12">
            <div id="travel-order-content"></div>
        </div>
    </div>
</div>
<?php
  Modal::begin([
    'id' => 'approve-modal',
    'size' => "modal-sm",
    'header' => '<div id="approve-modal-header"><h4>Approve Travel Order</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="approve-modal-content"></div>';
  Modal::end();
?>
<?php
Modal::begin([
  'id' => 'disapprove-modal',
  'size' => "modal-sm",
  'header' => '<div id="disapprove-modal-header"><h4>Disapprove Travel Order</h4></div>',
  'options' => ['tabindex' => false],
]);
echo '<div id="disapprove-modal-content"></div>';
Modal::end();
?>
<?php
Modal::begin([
  'id' => 'for-revision-modal',
  'size' => "modal-sm",
  'header' => '<div id="for-revision-modal-header"><h4>Open Travel Order For Editing</h4></div>',
  'options' => ['tabindex' => false],
]);
echo '<div id="for-revision-modal-content"></div>';
Modal::end();
?>
<style>
    #w0 tr td{
        line-height: 40px;
    }
    .to-menu{
        line-height: 20px; 
        font-weight: bold;
        padding-left: 20px;
    }

    .to-menu li{
        padding: 10px;
        cursor: pointer;
        color: black;
    }   

    .to-menu li.active{
        border-radius: 0.25rem;
        background-color: #EEF2FF;
        color: #6366F2;
    }
</style>
<?php
    $script = '
        $("#approve-button").click(function(){
            $("#approve-modal").modal("show").find("#approve-modal-content").load($(this).attr("value"));
        });  

        $("#disapprove-button").click(function(){
            $("#disapprove-modal").modal("show").find("#disapprove-modal-content").load($(this).attr("value"));
        });  

        $("#for-revision-button").click(function(){
            $("#for-revision-modal").modal("show").find("#for-revision-modal-content").load($(this).attr("value"));
        });  

        $(document).ready(function(){
            viewTravelInfo('.$model->TO_NO.');
        });

        function highlightMenu(menuClass)
        {
            if(menuClass === "travel"){ 
                $(".destination-menu").removeClass("active");    
                $(".vehicle-menu").removeClass("active");    
                $(".approval-menu").removeClass("active");    
                $(".travel-menu").addClass("active");   
            }else if(menuClass === "destination"){
                $(".travel-menu").removeClass("active");    
                $(".vehicle-menu").removeClass("active");
                $(".approval-menu").removeClass("active");    
                $(".destination-menu").addClass("active");    
            }else if(menuClass === "vehicle"){
                $(".destination-menu").removeClass("active");    
                $(".travel-menu").removeClass("active"); 
                $(".approval-menu").removeClass("active");    
                $(".vehicle-menu").addClass("active");
            }else if(menuClass === "approval"){
                $(".destination-menu").removeClass("active");    
                $(".travel-menu").removeClass("active"); 
                $(".vehicle-menu").removeClass("active");    
                $(".approval-menu").addClass("active");
            }
        }

        function viewTravelInfo(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/travel-order/travel-info']).'?id=" + id,
                beforeSend: function(){
                    $("#travel-order-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    highlightMenu("travel");
                    $("#travel-order-content").empty();
                    $("#travel-order-content").hide();
                    $("#travel-order-content").fadeIn("slow");
                    $("#travel-order-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function viewDestinationInfo(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/travel-order/destination-info']).'?id=" + id,
                beforeSend: function(){
                    $("#travel-order-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    highlightMenu("destination");
                    $("#travel-order-content").empty();
                    $("#travel-order-content").hide();
                    $("#travel-order-content").fadeIn("slow");
                    $("#travel-order-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function viewVehicleInfo(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/travel-order/vehicle-info']).'?id=" + id,
                beforeSend: function(){
                    $("#travel-order-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    highlightMenu("vehicle");
                    $("#travel-order-content").empty();
                    $("#travel-order-content").hide();
                    $("#travel-order-content").fadeIn("slow");
                    $("#travel-order-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function viewApprovalInfo(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/travel-order/approval-info']).'?id=" + id,
                beforeSend: function(){
                    $("#travel-order-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    highlightMenu("approval");
                    $("#travel-order-content").empty();
                    $("#travel-order-content").hide();
                    $("#travel-order-content").fadeIn("slow");
                    $("#travel-order-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function printTravelOrder()
        {
          var printWindow = window.open(
            "'.Url::to(['/v1/travel-order/print', 'id' => $model->TO_NO]).'", 
            "Print",
            "left=200", 
            "top=200", 
            "width=950", 
            "height=500", 
            "toolbar=0", 
            "resizable=0"
          );
          printWindow.addEventListener("load", function() {
              printWindow.print();
              setTimeout(function() {
                printWindow.close();
            }, 1);
          }, true);
        }
    ';

    $this->registerJs($script, View::POS_END);
?>
