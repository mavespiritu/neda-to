<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\Collapse;
?>
<h3 class="panel-title">Manage Items</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Manage your items here using the steps provided below.</p>
<?= !empty($model->menu) ? Collapse::widget(['items' => $model->menu, 'encodeLabels' => false, 'autoCloseItems' => true]) : 'No steps detected' ?>
<?php
    $script = '
        function selectItemMenu(id, j)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-menu']).'?id=" + id + "&j=" + j + "&menu=selectItem",
                beforeSend: function(){
                    $("#select-item-menu").html("<div class=\"text-center\"><svg class=\"spinner\" width=\"20px\" height=\"20px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#select-item-menu").empty();
                    $("#select-item-menu").hide();
                    $("#select-item-menu").fadeIn("slow");
                    $("#select-item-menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function groupItemMenu(id, j)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-menu']).'?id=" + id + "&j=" + j + "&menu=groupItem",
                beforeSend: function(){
                    $("#group-item-menu").html("<div class=\"text-center\"><svg class=\"spinner\" width=\"20px\" height=\"20px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#group-item-menu").empty();
                    $("#group-item-menu").hide();
                    $("#group-item-menu").fadeIn("slow");
                    $("#group-item-menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function aprMenu(id, j)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-menu']).'?id=" + id + "&j=" + j + "&menu=apr",
                beforeSend: function(){
                    $("#apr-menu").html("<div class=\"text-center\"><svg class=\"spinner\" width=\"20px\" height=\"20px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#apr-menu").empty();
                    $("#apr-menu").hide();
                    $("#apr-menu").fadeIn("slow");
                    $("#apr-menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function rfqMenu(id, j)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-menu']).'?id=" + id + "&j=" + j + "&menu=rfq",
                beforeSend: function(){
                    $("#rfq-menu").html("<div class=\"text-center\"><svg class=\"spinner\" width=\"20px\" height=\"20px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#rfq-menu").empty();
                    $("#rfq-menu").hide();
                    $("#rfq-menu").fadeIn("slow");
                    $("#rfq-menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function aoqMenu(id, j)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-menu']).'?id=" + id + "&j=" + j + "&menu=aoq",
                beforeSend: function(){
                    $("#aoq-menu").html("<div class=\"text-center\"><svg class=\"spinner\" width=\"20px\" height=\"20px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#aoq-menu").empty();
                    $("#aoq-menu").hide();
                    $("#aoq-menu").fadeIn("slow");
                    $("#aoq-menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function noaMenu(id, j)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-menu']).'?id=" + id + "&j=" + j + "&menu=noa",
                beforeSend: function(){
                    $("#noa-menu").html("<div class=\"text-center\"><svg class=\"spinner\" width=\"20px\" height=\"20px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#noa-menu").empty();
                    $("#noa-menu").hide();
                    $("#noa-menu").fadeIn("slow");
                    $("#noa-menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function poMenu(id, j)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-menu']).'?id=" + id + "&j=" + j + "&menu=po",
                beforeSend: function(){
                    $("#po-menu").html("<div class=\"text-center\"><svg class=\"spinner\" width=\"20px\" height=\"20px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#po-menu").empty();
                    $("#po-menu").hide();
                    $("#po-menu").fadeIn("slow");
                    $("#po-menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function ntpMenu(id, j)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-menu']).'?id=" + id + "&j=" + j + "&menu=ntp",
                beforeSend: function(){
                    $("#ntp-menu").html("<div class=\"text-center\"><svg class=\"spinner\" width=\"20px\" height=\"20px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#ntp-menu").empty();
                    $("#ntp-menu").hide();
                    $("#ntp-menu").fadeIn("slow");
                    $("#ntp-menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function orsMenu(id, j)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-menu']).'?id=" + id + "&j=" + j + "&menu=ors",
                beforeSend: function(){
                    $("#ors-menu").html("<div class=\"text-center\"><svg class=\"spinner\" width=\"20px\" height=\"20px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#ors-menu").empty();
                    $("#ors-menu").hide();
                    $("#ors-menu").fadeIn("slow");
                    $("#ors-menu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    ';

    $this->registerJs($script, View::POS_END);
?>
<style>
    .collapse-toggle{
        font-size: 13px;
    }
</style>