<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
?>

<div class="box box-primary">
    <div class="box-header panel-title"><i class="fa fa-edit"></i>Select RIS Items</div>
    <div class="box-body">
        <p><i class="fa fa-exclamation-circle"></i> Select items from approved RISs to include in PR.</p>
        <?= $this->render('_items-ris_form', [
            'model' => $model,
            'rises' => $rises
        ]) ?>
        <div id="ris-items"></div>
    </div>
</div>
<div class="box box-primary">
    <div class="box-header panel-title"><i class="fa fa-list"></i>PR Items</div>
    <div class="box-body">
        <div id="pr-items"></div>
    </div>
</div>
<?php
    $script = '
        function prItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr-items']).'?id=" + id,
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
                url: "'.Url::to(['/v1/pr/load-ris-items']).'?id=" + id + "&ris_id=" + ris_id,
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
            prItems('.$model->id.');
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>