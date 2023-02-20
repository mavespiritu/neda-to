<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
?>

<table style="width: 100%; line-height: 30px;" class="table-hover table-responsive">
    <tr onclick="items(<?= $model->id ?>)">
        <td style="width: 10%;"><a href="javascript:void(0);"><?= $j ?>.1</a></td>
        <td style="width: 85%;"><a href="javascript:void(0);">Select Items from RIS</a></td>
        <td style="width: 5%;" align=right><?= $model->prItems ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
    <tr onclick="lot(<?= $model->id ?>)">
        <td style="width: 10%;"><a href="javascript:void(0);"><?= $j ?>.2</a></td>
        <td style="width: 85%;"><a href="javascript:void(0);">Manage Lot (<?= number_format($model->getLots()->count(), 0) ?>)</a></td>
        <td style="width: 5%;" align=right><?= $model->lots ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
    <tr onclick="previewPr(<?= $model->id ?>)">
        <td style="width: 10%;"><a href="javascript:void(0);"><?= $j ?>.3</a></td>
        <td style="width: 85%;"><a href="javascript:void(0);">Preview PR</a></td>
        <td style="width: 5%;" align=right><?= !is_null($model->date_prepared) ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
    <tr onclick="printPr(<?= $model->id ?>)">
        <td style="width: 10%;"><a href="javascript:void(0);"><?= $j ?>.4</a></td>
        <td style="width: 85%;"><a href="javascript:void(0);">Print PR</a></td>
        <td style="width: 5%;" align=right>&nbsp;</td>
    </tr>
</table>
<?php
    $script = '
        function lot(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/lot']).'?id=" + id,
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

        function items(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/items']).'?id=" + id,
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

        function previewPr(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/pr']).'?id=" + id,
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

        function printPr(id)
        {
          var printWindow = window.open(
            "'.Url::to(['/v1/pr/print-pr']).'?id=" + id, 
            "Print",
            "left=200", 
            "top=200", 
            "width=650", 
            "height=500", 
            "toolbar=0", 
            "resizable=0"
          );
          printWindow.addEventListener("load", function() {
              printWindow.print();
              setTimeout(function() {
                printWindow.close();
            }, 1);
          }, true);
        }
    ';

    $this->registerJs($script, View::POS_END);
?>