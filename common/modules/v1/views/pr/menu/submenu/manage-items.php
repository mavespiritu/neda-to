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
<style>
    .collapse-toggle{
        font-size: 13px;
    }
</style>