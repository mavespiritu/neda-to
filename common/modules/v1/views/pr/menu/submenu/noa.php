<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<table style="width: 100%; line-height: 30px;" class="table-hover table-responsive">
<?php if($bids){ ?>
    <?php $i = 1;?>
    <?php foreach($bids as $bid){ ?>
        <tr>
            <td style="width: 10%;"><?= $j ?>.<?= $i ?></td>
            <td colspan=2>AOQ No. <?= $bid->bid_no ?></td>
        </tr>
        <?php if($bid->winners){ ?>
            <?php $k = 1; ?>
            <?php foreach($bid->winners as $winner){ ?>
                <tr onclick="noaWinner('<?= $model->id?>','<?= $bid->id?>','<?= $winner->id?>','<?= $j ?>','<?= $i ?>','<?= $k ?>');">
                    <td style="width: 10%;">&nbsp;</td>
                    <td style="width: 85%;"><a href="javascript:void(0);"><?= $j ?>.<?= $i ?>.<?= $k ?> <?= $winner->business_name ?></a></td>
                    <td style="width: 5%;" align=right><?= $bid->getNoaForWinner($winner->id) ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
                </tr>
                <?php $k++ ?>
            <?php } ?>
        <?php } ?>
        <?php $i++ ?>
    <?php } ?>
<?php }else{ ?>
    <tr>
        <td colspan=3>No bid conducted.</td>
    </tr>
<?php } ?>
</table>
<?php
    $script = '
        function noaWinner(id, bid_id, supplier_id, j, i, k)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/create-noa']).'?id=" + id + "&bid_id=" + bid_id + "&supplier_id=" + supplier_id + "&j=" + j + "&i=" + i + "&k=" + k,
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