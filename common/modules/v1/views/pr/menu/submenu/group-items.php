<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<h4>2. Group Items</h4>
<p><i class="fa fa-exclamation-circle"></i> Group items for agency procurement, supplier, and direct obligation.</p>
<ul class="products-list product-list-in-box navigation">
    <li class="item">
        <div class="product-img">
            <div class="circle">2.1</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="groupAprItems(<?= $model->id?>);" class="product-title">Set Agency Procurement Items
            <span class="badge bg-green pull-right"><?= $model->aprItemCount ?></span>
            </a>
            <span class="product-description">Select items from PR to include in APR</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">2.2</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="groupRfqItems(<?= $model->id?>);" class="product-title">Set Supplier Items
            <span class="badge bg-green pull-right"><?= $model->rfqItemCount ?></span>
            </a>
            <span class="product-description">Select items from PR to include in RFQ</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">2.3</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="groupNonProcurableItems(<?= $model->id?>);" class="product-title">Set Non-procurable Items
            <span class="badge bg-green pull-right"><?= $model->nonProcurableItemCount ?></span>
            </a>
            <span class="product-description">Select items from PR for direct obligation</span>
        </div>
    </li>
</ul>
<?php
    $script = '
        function groupAprItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/group-apr-items']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-container").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-container").empty();
                    $("#pr-container").hide();
                    $("#pr-container").fadeIn("slow");
                    $("#pr-container").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function groupRfqItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/group-rfq-items']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-container").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-container").empty();
                    $("#pr-container").hide();
                    $("#pr-container").fadeIn("slow");
                    $("#pr-container").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }     

        function groupNonProcurableItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/group-non-procurable-items']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-container").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-container").empty();
                    $("#pr-container").hide();
                    $("#pr-container").fadeIn("slow");
                    $("#pr-container").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }     
    ';

    $this->registerJs($script, View::POS_END);
?>