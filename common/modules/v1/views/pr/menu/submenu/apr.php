<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<table style="width: 100%; line-height: 30px;" class="table-hover table-responsive">
    <tr onclick="aprQuotation(<?= $model->id ?>)">
        <td style="width: 10%;"><a href="javascript:void(0);"><?= $j ?>.1</a></td>
        <td style="width: 85%;"><a href="javascript:void(0);">Request APR</a></td>
        <td style="width: 5%;" align=right><?= $model->apr ? !is_null($model->apr->date_prepared) ? '<i class="fa fa-check text-green"></i>' : '' : '' ?></td>
    </tr>
    <tr onclick="printApr(<?= $model->id ?>)">
        <td><a href="javascript:void(0);"><?= $j ?>.2</a></td>
        <td><a href="javascript:void(0);">Print APR</a></td>
        <td align=right>&nbsp;</td>
    </tr>
    <tr onclick="aprRetrieveQuotation(<?= $model->id ?>)">
        <td><a href="javascript:void(0);"><?= $j ?>.3</a></td>
        <td><a href="javascript:void(0);">Retrieve APR</a></td>
        <td align=right><?= $model->apr ? !empty($model->apr->aprItemCosts) ? '<i class="fa fa-check text-green"></i>' : '' : '' ?></td>
    </tr>
</table>
<?php
    $script = '
    function aprQuotation(id)
    {
        $.ajax({
            url: "'.Url::to(['/v1/pr/request-apr']).'?id=" + id,
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

    function aprRetrieveQuotation(id)
    {
        $.ajax({
            url: "'.Url::to(['/v1/pr/retrieve-apr']).'?id=" + id,
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

    function printApr(id)
    {
        var printWindow = window.open(
        "'.Url::to(['/v1/pr/print-apr']).'?id=" + id, 
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