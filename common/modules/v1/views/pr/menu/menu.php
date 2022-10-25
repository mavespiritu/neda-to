<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<p><b>KEY STEPS</b></p>
<ul class="products-list product-list-in-box navigation">
    <li class="item">
        <div class="product-img">
            <div class="circle">1</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="items(<?= $model->id?>);" class="product-title">Select Items
            <span class="badge bg-green pull-right"><?= $model->itemCount ?></span>
            </a>
            <span class="product-description">Select items from approved RIS</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">2</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="groupItems(<?= $model->id?>);" class="product-title">Group Items</a>
            <span class="product-description">Group selected items to prepare APR and RFQ</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">3</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="setQuotations(<?= $model->id?>);" class="product-title">Request Quotation
            <span class="badge bg-green pull-right"><?= $model->aprCount + $model->rfqCount ?></span>
            </a>
            <span class="product-description">Create RFQ for suppliers</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">4</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="retrieveQuotations(<?= $model->id?>);" class="product-title">Retrieve Quotation</a>
            <span class="product-description">Input prices from suppliers</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">5</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onClick="bidItems(<?= $model->id ?>);" class="product-title">Canvas/Bid Items</a>
            <span class="product-description">Select winning suppliers on each items</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">6</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="createPurchaseOrders(<?= $model->id ?>);"  class="product-title">Create Purchase Order/Contract</a>
            <span class="product-description">Create purchase order or contract</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">7</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" class="product-title">Inspect Items</a>
            <span class="product-description">Inspect delivered items</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">8</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" class="product-title">Issue Items</a>
            <span class="product-description">Issue inspected items</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">9</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="generateReports(<?= $model->id ?>);" class="product-title">Generate Reports</a>
            <span class="product-description">Generate reports related to purchase request</span>
        </div>
    </li>
</ul>

<br>
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
    </ul>
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

        function home(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/home']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").html(data);
                    updateNavigation("home-link");
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function items(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/items']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").html(data);
                    updateNavigation("items-link");
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

        function createPurchaseOrders(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=createPurchaseOrders",
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