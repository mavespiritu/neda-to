<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<ul class="navigation" type="none" style="line-height: 3rem;">
    <li><a href="javascript:void(0);" onclick="home(<?= $model->id?>);" class="home-link">Home</a></li>
    <li><a href="javascript:void(0);" onclick="items(<?= $model->id?>);" class="items-link">Items (<?= $model->itemCount ?>)</a></li>
    <li><a href="javascript:void(0);" onclick="dbmItems(<?= $model->id?>);" class="dbm-link">Item Grouping</a></li>
    <li><a href="javascript:void(0);" onclick="dbmPricing(<?= $model->id?>);" class="dbm-price-link">DBM-PS Pricing</a></li>
    <li><a href="javascript:void(0);" onclick="quotations(<?= $model->id?>);" class="quotations-link">Set Quote (<?= $model->rfqCount ?>)</a></li>
    <li><a href="javascript:void(0);" onclick="retrieveQuotations(<?= $model->id?>);" class="retrieve-quotation-link">Retrieve and Bid</a></li>
    <?php if($model->type == 'Supply'){ ?>
        <li><a href="javascript:void(0);" onClick="purchaseOrders(<?= $model->id?>);" class="po-link">Purchase Orders</a></li>
    <?php }else{ ?>
        <li><a href="javascript:void(0);" class="contracts-link">Contracts</a></li>
    <?php } ?>
    <li><a href="javascript:void(0);" class="inspection-link">Inspection</a></li>
    <li><a href="javascript:void(0);" class="issuance-link">Issuance</a></li>
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

        function dbmItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/dbm-items']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").html(data);
                    updateNavigation("dbm-link");
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function dbmPricing(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/dbm-pricing']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").html(data);
                    updateNavigation("dbm-price-link");
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function quotations(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/quotation']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").html(data);
                    updateNavigation("quotations-link");
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function retrieveQuotations(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/retrieve-quotation']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").html(data);
                    updateNavigation("retrieve-quotation-link");
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function purchaseOrders(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/purchase-order']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").html(data);
                    updateNavigation("po-link");
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
    ul.navigation,
    ul.todos,
    ul.reports
    {
        padding-left: 2px;
        margin-left: 2px;
    }

    ul.navigation > li > a,
    ul.todos > li > a,
    ul.reports > li > a
    {
        padding-left: 2px;
    }

    ul.navigation > li > a.active
    {
        font-weight: bolder;
        border-left: 3px solid #3C8DBC;
    }

    ul.navigation > li > a:hover
    {
        font-weight: bolder;
        border-left: 3px solid #3C8DBC;
    }
</style>