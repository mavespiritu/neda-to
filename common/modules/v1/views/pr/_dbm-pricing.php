<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
?>

<div class="box box-primary">
    <div class="box-header panel-title"><i class="fa fa-list"></i>Set DBM-PS Pricing</div>
    <div class="box-body">
        <p><i class="fa fa-exclamation-circle"></i> Input current price stated by the DBM Available Items. Remove items not available by clicking <a href="javascript:void(0);" onclick="dbmItems(<?= $model->id?>);" class="dbm-link">here</a>.</p>
        <div id="for-dbm-pricing"></div>
    </div>
</div>
<?php
    $script = '
        function viewDbmPrices(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/view-dbm-price']).'?id=" + id,
                beforeSend: function(){
                    $("#for-dbm-pricing").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#for-dbm-pricing").empty();
                    $("#for-dbm-pricing").hide();
                    $("#for-dbm-pricing").fadeIn("slow");
                    $("#for-dbm-pricing").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            viewDbmPrices('.$model->id.');
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>