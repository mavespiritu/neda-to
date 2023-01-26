<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
?>

<h3 class="panel-title">1. Select Items</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Select items from approved RIS to include in PR.</p>
<div class="row">
    <div class="col-md-12 col-xs-12">
        <?= $this->render('ris_form', [
            'model' => $model,
            'rises' => $rises
        ]) ?>
        <div id="ris-items"></div>
    </div>
    <!-- <div class="col-md-6 col-xs-12">
        <h4>PR Items</h4>
        <div id="pr-items"></div>
    </div> -->
</div>
<?php
    $script = '
        function prItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/select-pr-items']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-items").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-items").empty();
                    $("#pr-items").hide();
                    $("#pr-items").fadeIn("slow");
                    $("#pr-items").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function loadRisItems(id, ris_id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/select-ris-items']).'?id=" + id + "&ris_id=" + ris_id,
                beforeSend: function(){
                    $("#ris-items").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#ris-items").empty();
                    $("#ris-items").hide();
                    $("#ris-items").fadeIn("slow");
                    $("#ris-items").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            //prItems('.$model->id.');
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>