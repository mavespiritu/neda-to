<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<table style="width: 100%; line-height: 30px;" class="table-hover table-responsive">
    <tr onclick="rfqQuotation(<?= $model->id ?>)">
        <td style="width: 10%;"><a href="javascript:void(0);"><?= $j ?>.1</a></td>
        <td style="width: 85%;"><a href="javascript:void(0);">Generate RFQ</a></td>
        <td style="width: 5%;" align=right><?= $model->rfqs ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
    <tr onclick="rfqRetrieveQuotation(<?= $model->id ?>)">
        <td><a href="javascript:void(0);"><?= $j ?>.2</a></td>
        <td><a href="javascript:void(0);">Retrieve RFQ</a></td>
        <td align=right><?= $model->rfqInfos ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
</table>
<?php
    $script = '
        function rfqQuotation(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/request-rfq']).'?id=" + id,
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

        function rfqRetrieveQuotation(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/retrieve-rfq']).'?id=" + id,
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