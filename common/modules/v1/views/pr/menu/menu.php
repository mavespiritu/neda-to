<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<a onclick="items(<?= $model->id?>);" class="btn btn-app main-menu" id="step-1">
    <?= $model->itemCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-shopping-cart"></i>1. Select Items
</a>
<a onclick="groupItems(<?= $model->id?>);" class="btn btn-app main-menu" id="step-2">
    <?= $model->itemCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-sort"></i>2. Group Items
</a>
<a onclick="setQuotations(<?= $model->id?>);" class="btn btn-app main-menu" id="step-3">
    <?= $model->aprCount + $model->rfqCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-envelope-o"></i>3. RFQs/APR
</a>
<a onclick="retrieveQuotations(<?= $model->id?>);" class="btn btn-app main-menu" id="step-4">
    <?= $model->aprInfoCount + $model->rfqInfoCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-envelope-open-o"></i>4. Retrieve <br> RFQs/APR
</a>
<a onclick="bidItems(<?= $model->id?>);" class="btn btn-app main-menu" id="step-5">
    <?= $model->bidCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-legal"></i>5. AOQ
</a>
<a onclick="award(<?= $model->id?>);" class="btn btn-app main-menu" id="step-6">
    <?= $model->noaCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-handshake-o"></i>6. NOA
</a>
<a onclick="createPurchaseOrderOrContract(<?= $model->id?>);" class="btn btn-app main-menu" id="step-7">
    <?= $model->poCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-edit"></i>7. POs/Contracts
</a>
<a onclick="proceed(<?= $model->id?>);" class="btn btn-app main-menu" id="step-8">
    <?= $model->ntpCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-arrow-right"></i>8. NTP
</a>
<a onclick="obligateItems(<?= $model->id?>);" class="btn btn-app main-menu" id="step-9">
    <?= $model->orsCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-table"></i>9. ORS
</a>
<a onclick="inspectItems(<?= $model->id?>);" class="btn btn-app main-menu" id="step-9">
    <?= $model->iarCount > 0 ? '<span class="badge bg-green"><i class="fa fa-check"></i></span>' : '' ?>
    <i class="fa fa-check"></i>9. Inspect Items
</a>

<!-- <br>
<p><b>QUICK ACCESS</b></p>
    <li><a href="javascript:void(0);" onclick="home(<?= $model->id?>);" class="home-link">Home</a></li>
    <li><a href="javascript:void(0);" onclick="items(<?= $model->id?>);" class="items-link">Select Items (<?= $model->itemCount ?>)</a></li>
    <li><a href="javascript:void(0);" onclick="dbmItems(<?= $model->id?>);" class="group-link">Group Items</a></li>
    <li><a href="javascript:void(0);" onclick="dbmPricing(<?= $model->id?>);" class="dbm-price-link">DBM-PS Pricing</a></li>
    <li><a href="javascript:void(0);" onclick="quotations(<?= $model->id?>);" class="quotations-link">Set Quote (<?= $model->rfqCount ?>)</a></li>
    <li><a href="javascript:void(0);" onclick="retrieveQuotations(<?= $model->id?>);" class="retrieve-quotation-link">Retrieve and Bid</a></li>
    <?php if($model->type == 'Supply'){ ?>
        <li><a href="javascript:void(0);" onclick="createPurchaseOrders(<?= $model->id?>);" class="po-link">Purchase Orders</a></li>
    <?php }else{ ?>
        <li><a href="javascript:void(0);" class="contracts-link">Contracts</a></li>
    <?php } ?>
    <li><a href="javascript:void(0);" class="inspection-link">Inspection</a></li>
    <li><a href="javascript:void(0);" class="issuance-link">Issuance</a></li>
</ul>
<ul class="reports" style="font-size: 13px; line-height: 2rem;" type="none">
        <li><?= Html::button('<i class="fa fa-print"></i> Purchase Request (PR)', ['value' => Url::to(['/v1/pr/pr', 'id' => $model->id]), 'class' => 'button-link', 'id' => 'pr-button']) ?></li>
        <li><?= Html::button('<i class="fa fa-print"></i> Agency Purchase Request (APR)', ['value' => Url::to(['/v1/pr/apr', 'id' => $model->id]), 'class' => 'button-link', 'id' => 'apr-button']) ?></li>
        <li><?= Html::button('<i class="fa fa-print"></i> Request For Quotation (RFQ)', ['value' => Url::to(['/v1/pr/rfq', 'id' => $model->id]), 'class' => 'button-link', 'id' => 'rfq-button']) ?></li>
        <li><?= Html::button('<i class="fa fa-print"></i> Abstract of Quotation (AOQ)', ['value' => Url::to(['/v1/pr/aoq', 'id' => $model->id]), 'class' => 'button-link', 'id' => 'aoq-button']) ?></li>
        <?php if($model->type == 'Supply'){ ?>
        <li><i class="fa fa-print"></i> Purchase Order (PO)</li>
        <?php }else{ ?>
        <li><i class="fa fa-print"></i> Contract</li>
        <?php } ?>
        <li><i class="fa fa-print"></i> Notice of Award (NOA)</li>
        <li><i class="fa fa-print"></i> Notice to Proceed (NTP)</li>
        <li><i class="fa fa-print"></i> Obligation Request Status (ORS)</li>
        <li><i class="fa fa-print"></i> Disbursement Voucher (DV)</li>
        <li><i class="fa fa-print"></i> Inspection and Acceptance Report (IAR)</li>
    </ul> -->
<?php
    $script = '
        function updateNavigation(className)
        {
            $(".navigation li a").each(function(i)
            {
                if($(this).hasClass(className))
                {
                    if($(this).hasClass("active") == false)
                    {
                        $(this).addClass("active");
                    }
                }else{
                    $(this).removeClass("active");
                }
            });
        }

        function items(id, ctr)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/items']).'?id=" + id + "&ctr=" + ctr,
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

        function groupItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=groupItems",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function setQuotations(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=setQuotations",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function retrieveQuotations(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=retrieveQuotations",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function bidItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=bidItems",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function createPurchaseOrderOrContract(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=createPurchaseOrderOrContract",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function proceed(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=proceed",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function award(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=award",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function obligateItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=obligateItems",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function inspectItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=inspectItems",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function generateReports(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=generateReports",
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").append("<div class=\"row\"><div class=\"col-md-3 col-xs-12\" id=\"pr-submenu\"></div><div class=\"col-md-9 col-xs-12\" id=\"pr-container\"></div></div>");
                    $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            $("#update-button").click(function(){
              $("#update-modal").modal("show").find("#update-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>
<style>
    .navigation {

    }
    .circle {
        border-radius: 50%;
        width: 40px;
        height: 40px;
        padding: 10px;
        background: #3B82F6;
        color: white;
        text-align: center;
      }
    
    li.item{
        border: none !important;
        padding: 10px auto !important;
    }

</style>