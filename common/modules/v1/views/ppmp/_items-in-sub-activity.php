<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use common\modules\v1\models\PpmpItem;
use fedemotta\datatables\DataTables;
?>

<?= $dataProvider->getTotalCount() > 0 ? Gridview::widget([
    'options' => [
        'class' => 'table-responsive table-condensed table-bordered table-striped table-hover content-table',
        'style' => 'max-height: 500px;',
        'id' => 'table-subactivity-'.$subActivity->id,
    ],
    'dataProvider' => $dataProvider,
    'showFooter' => true,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn',
            'headerOptions' => ['style' => 'width: 3%;'],
        ],
        [
            'header' => 'Type', 
            'attribute' => 'type',
        ],
        [
            'header' => 'Object', 
            'format' => 'raw',
            'value' => function($item){
                return $item->obj->code.'<br>'.$item->obj->title;
            }
        ],
        [
            'header' => 'Title', 
            'format' => 'raw',
            'value' => function($item){
                return $item->item->title;
            }
        ],
        'item.unit_of_measure',
        [
            'header' => '&nbsp;', 
            'format' => 'raw', 
            'value' => function($item){
                return '<u>T</u> <br> <u>U</u> <br> <b>R</b>';
            }
        ],
        [
            'header' => 'J', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(1), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(1), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(1) - $item->getQuantityUsedPerMonth(1), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'F', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(2), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(2), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(2) - $item->getQuantityUsedPerMonth(2), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'M', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(3), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(3), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(3) - $item->getQuantityUsedPerMonth(3), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'A', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(4), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(4), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(4) - $item->getQuantityUsedPerMonth(4), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'M', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(5), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(5), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(5) - $item->getQuantityUsedPerMonth(5), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'J', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(6), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(6), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(6) - $item->getQuantityUsedPerMonth(6), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'J', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(7), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(7), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(7) - $item->getQuantityUsedPerMonth(7), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'A', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(8), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(8), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(8) - $item->getQuantityUsedPerMonth(8), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'S', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(9), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(91), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(9) - $item->getQuantityUsedPerMonth(9), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'O', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(10), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(10), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(10) - $item->getQuantityUsedPerMonth(10), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'N', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(11), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(11), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(11) - $item->getQuantityUsedPerMonth(11), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'D', 
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->getQuantityPerMonth(12), 0).'</u> <br>';
                $value .= '<u>'.number_format($item->getQuantityUsedPerMonth(12), 0).'</u> <br>';
                $value .= '<b>'.number_format($item->getQuantityPerMonth(12) - $item->getQuantityUsedPerMonth(12), 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'Total Qty', 
            'attribute' => 'quantity',
            'format' => 'raw', 
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                $value = '';
                $value .= '<u>'.number_format($item->quantities, 0).'</u> <br>';
                $value .= '<u>'.number_format($item->quantityUsed, 0).'</u> <br>';
                $value .= '<b>'.number_format($item->remainingQuantity, 0).'</b>';
                return $value;
            }
        ],
        [
            'header' => 'Cost Per Unit', 
            'attribute' => 'cost',
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                return number_format($item->cost, 2);
            },
        ],
        [
            'header' => 'Total', 
            'attribute' => 'totalCost',
            'contentOptions' => ['style' => 'text-align: right;'],
            'footerOptions' => ['style' => 'text-align: right;'],
            'value' => function($item){
                return number_format($item->totalCost, 2);
            },
            'footer' => PpmpItem::pageQuantityTotal($dataProvider->models, 'totalCost'),
        ],
        [
            'header' => 'Remarks', 
            'attribute' => 'remarks',
        ],
        [
            'format' => 'raw', 
            'headerOptions' => ['style' => 'width:50px;'],
            'value' => function($item) use ($subActivity, $model){
                $buttons = '';
                $buttons .= $model->status ? $model->status->status != 'Approved' ? Html::button('<i class="fa fa-edit"></i>', ['value' => Url::to(['/v1/ppmp/update-item', 'id' => $item->id]), 'class' => 'btn btn-primary btn-block btn-xs update-item-button-'.$subActivity->id]) : '' : Html::button('<i class="fa fa-edit"></i>', ['value' => Url::to(['/v1/ppmp/update-item', 'id' => $item->id]), 'class' => 'btn btn-primary btn-xs btn-block update-item-button-'.$subActivity->id]);
                $buttons .= $model->status ? $model->status->status != 'Approved' ? Html::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-danger btn-block btn-xs', 'onClick' => 'deleteItem('.$item->id.')']) : '' : Html::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-danger btn-block btn-xs', 'onClick' => 'deleteItem('.$item->id.')']);
                return $buttons;
            }
        ],
    ],
]) : '<p class="text-center">No items found</p>' ?>

<?php
  Modal::begin([
    'id' => 'update-item-modal-'.$subActivity->id,
    'size' => "modal-lg",
    'header' => '<div id="update-item-modal-header-'.$subActivity->id.'"><h4>Update Item</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-item-modal-content-'.$subActivity->id.'"></div>';
  Modal::end();
?>

<?php
    $script = '
        function loadItems(id, activity_id, fund_source_id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/ppmp/load-items']).'",
                data: {
                    id: id,
                    activity_id: activity_id,
                    fund_source_id: fund_source_id,
                },
                beforeSend: function(){
                    $("#items").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    $("#items").empty();
                    $("#items").hide();
                    $("#items").fadeIn("slow");
                    $("#items").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function deleteItem(id)
        {
            var con = confirm("Are you sure you want to delete this item?");
            if(con == true)
            { 
                $.ajax({
                    url: "'.Url::to(['/v1/ppmp/delete-item']).'?id="+ id,
                    method: "POST",
                    success: function (data) {
                        alert("Item Deleted");
                        loadItems('.$model->id.','.$activity->id.','.$fundSource->id.');
                        loadPpmpTotal('.$model->id.');
                        loadOriginalTotal('.$model->id.');
                        loadSupplementalTotal('.$model->id.');
                        loadItemSummary('.$model->id.');
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        }

        $(document).ready(function(){
            $(".update-item-button-'.$subActivity->id.'").click(function(){
                //$("#update-item-modal-'.$subActivity->id.'").modal("show").find("#update-item-modal-content-'.$subActivity->id.'").load($(this).attr("value"));
                $("html").animate({ scrollTop: 0 }, "slow");
                $("#item-form-container").load($(this).attr("value"));
                $("#close-item-form-button").css("display", "block");
                $("#create-item-button").css("display", "none");
            });

            $(".content-table").freezeTable({
                "scrollable": true,
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>