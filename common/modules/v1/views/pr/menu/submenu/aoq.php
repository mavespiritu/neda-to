<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<table style="width: 100%; line-height: 30px;" class="table-hover table-responsive">
<?php if($rfqs){ ?>
    <?php $i = 1;?>
    <?php foreach($rfqs as $rfq){ ?>
        <tr onclick="bidRfq('<?= $model->id?>','<?= $rfq->id?>','<?= $i ?>');">
            <td style="width: 10%;"><a href="javascript:void(0);"><?= $j ?>.<?= $i ?></a></td>
            <td style="width: 85%;"><a href="javascript:void(0);">RFQ No. <?= $rfq->rfq_no ?></a></td>
            <td style="width: 5%;" align=right>&nbsp;</td>
        </tr>
        <?php $i++ ?>
    <?php } ?>
<?php } ?>
</table>
<?php
    $script = '
        function bidRfq(id, rfq_id, i)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/bid-rfq']).'?id=" + id + "&rfq_id=" + rfq_id + "&i=" + i,
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