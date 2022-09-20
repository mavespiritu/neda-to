<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
?>

<div class="box box-primary">
    <div class="box-header panel-title"><i class="fa fa-list"></i>For APR</div>
    <div class="box-body">
        <p><i class="fa fa-exclamation-circle"></i> Items included in this table will be subjected for APR to DBM.</p>
        <div id="for-apr-items"></div>
    </div>
</div>
<div class="box box-primary">
    <div class="box-header panel-title"><i class="fa fa-list"></i>For RFQ</div>
    <div class="box-body">
        <p><i class="fa fa-exclamation-circle"></i> Items included in this table will be subjected for RFQ.</p>
        <div id="for-rfq-items"></div>
    </div>
</div>
<?php
    $script = '
        function aprItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/apr-items']).'?id=" + id,
                beforeSend: function(){
                    $("#for-apr-items").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#for-apr-items").empty();
                    $("#for-apr-items").hide();
                    $("#for-apr-items").fadeIn("slow");
                    $("#for-apr-items").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function rfqItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/rfq-items']).'?id=" + id,
                beforeSend: function(){
                    $("#for-rfq-items").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#for-rfq-items").empty();
                    $("#for-rfq-items").hide();
                    $("#for-rfq-items").fadeIn("slow");
                    $("#for-rfq-items").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            aprItems('.$model->id.');
            rfqItems('.$model->id.');
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>