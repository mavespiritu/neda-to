<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<table style="width: 100%; line-height: 30px;" class="table-hover table-responsive">
<?php $i = 1; ?>
<?php if($model->nonProcurableItems){ ?>
    <?php $k = 1; ?>
    <tr>
        <td style="width: 10%;"><?= $j ?>.<?= $i ?></td>
        <td colspan=2>Non-Procurable Items of PR No. <?= $model->pr_no ?></td>
    </tr>
    <tr onclick="obligatePo('<?= $model->id ?>','<?= null ?>','<?= null ?>','<?= $j ?>','<?= $i ?>','<?= $k ?>','NP');">
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 85%;"><a href="javascript:void(0);">ORS for Non-procurable Items</a></td>
        <td style="width: 5%;" align=right><?= $model->getItemsHasOrs('null', 'null', 'NP') ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
    <?php $i++ ?>
<?php } ?>
<?php if($apr){ ?>
    <?php $k = 1; ?>
    <tr>
        <td style="width: 10%;"><?= $j ?>.<?= $i ?></td>
        <td colspan=2>APR No. <?= $model->pr_no ?></td>
    </tr>
    <tr onclick="obligatePo('<?= $model->id ?>','<?= $model->apr->id ?>','<?= null ?>','<?= $j ?>','<?= $i ?>','<?= $k ?>','APR');">
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 85%;"><a href="javascript:void(0);">ORS for APR No. <?= $model->pr_no ?></a></td>
        <td style="width: 5%;" align=right><?= $model->getItemsHasOrs($model->apr->id, 'null', 'APR') ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
    <?php $i++ ?>
<?php } ?>
<?php if($bids){ ?>
    <?php foreach($bids as $bid){ ?>
        <tr>
            <td style="width: 10%;"><?= $j ?>.<?= $i ?></td>
            <td colspan=2>Canvas/Bid No. <?= $bid->bid_no ?></td>
        </tr>
        <?php if($bid->winners){ ?>
            <?php $k = 1; ?>
            <?php foreach($bid->winners as $winner){ ?>
                <tr>
                    <td style="width: 10%;">&nbsp;</td>
                    <td style="width: 85%;"><?= $j ?>.<?= $i ?>.<?= $k ?> <?= $winner->business_name ?></td>
                    <td style="width: 5%;" align=right>&nbsp;</td>
                </tr>
                <?php if($winner->getPos($bid->id)){ ?>
                    <?php foreach($winner->getPos($bid->id) as $po){ ?>
                        <tr onclick="obligatePo('<?= $model->id ?>','<?= null ?>','<?= $po->id ?>','<?= $j ?>','<?= $i ?>','<?= $k ?>','PO');">
                            <td style="width: 10%;">&nbsp;</td>
                            <td style="width: 85%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);">ORS for <?= $po->type == 'PO' ? 'PO No.' : 'Contract No.' ?> <?= $po->pocnNo ?></a></td>
                            <td style="width: 5%;" align=right><?= $model->getItemsHasOrs('null', $po->id, 'PO') ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
                        </tr>
                        <?php $k++ ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
        <?php $i++ ?>
    <?php } ?>
<?php }else{ ?>
    <tr>
        <td colspan=3 align=center>No set non-procurable items or no bidding conducted. Include items <a href="javascript:void(0)" onclick="items(<?= $model->id ?>)">here</a>.</td>
    </tr>
<?php } ?>
</table>
<?php
    $script = '
        function obligatePo(id, apr_id, po_id, j, i, k, type)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/obligate-item']).'?id=" + id + "&apr_id=" + apr_id + "&po_id=" + po_id + "&j=" + j + "&i=" + i + "&k=" + k + "&type=" + type,
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