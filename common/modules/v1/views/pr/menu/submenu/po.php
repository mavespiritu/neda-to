<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<table style="width: 100%; line-height: 30px;" class="table-hover table-responsive">
<?php $i = 1; ?>
<!-- Activate if PO is also given to DBM -->
<!-- <?php if($apr){ ?>
    <?php $k = 1; ?>
    <tr>
        <td style="width: 10%;"><?= $j ?>.<?= $i ?></td>
        <td colspan=2>APR No. <?= $model->pr_no ?></td>
    </tr>
    <tr>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 85%;"><?= $j ?>.<?= $i ?>.<?= $k ?> <?= $supplier->business_name ?></td>
        <td style="width: 5%;" align=right><?= $model->getSupplierHasPoOrContract(null, $supplier->id) ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
    <tr onclick="createPurchaseOrder('<?= $model->id ?>','null','<?= $supplier->id ?>','<?= $j ?>','<?= $i ?>','<?= $k ?>');">
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 85%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);">Preview Purchase Order (PO)</a></td>
        <td style="width: 5%;" align=right><?= $model->getSupplierHasPo(null, $supplier->id, 'PO') ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
    <tr onclick="createContract('<?= $model->id ?>','null','<?= $supplier->id ?>','<?= $j ?>','<?= $i ?>','<?= $k ?>');">
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 85%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);">Preview Contract</a></td>
        <td style="width: 5%;" align=right><?= $model->getSupplierHasPo(null, $supplier->id, 'Contract') ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
    </tr>
    <?php //$i++ ?>
<?php } ?> -->
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
                    <td style="width: 5%;" align=right><?= $model->getSupplierHasPoOrContract($bid->id, $winner->id) ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
                </tr>
                <tr onclick="createPurchaseOrder('<?= $model->id ?>','<?= $bid->id?>','<?= $winner->id ?>','<?= $j ?>','<?= $i ?>','<?= $k ?>');">
                    <td style="width: 10%;">&nbsp;</td>
                    <td style="width: 85%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);">Preview Purchase Order (PO)</a></td>
                    <td style="width: 5%;" align=right><?= $model->getSupplierHasPo($bid->id, $winner->id, 'PO') ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
                </tr>
                <tr onclick="createContract('<?= $model->id ?>','<?= $bid->id?>','<?= $winner->id ?>','<?= $j ?>','<?= $i ?>','<?= $k ?>');">
                    <td style="width: 10%;">&nbsp;</td>
                    <td style="width: 85%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);">Preview Contract</a></td>
                    <td style="width: 5%;" align=right><?= $model->getSupplierHasPo($bid->id, $winner->id, 'Contract') ? '<i class="fa fa-check text-green"></i>' : '' ?></td>
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
        function selectType(id, bid_id, supplier_id, j, i, k)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/select-type']).'?id=" + id + "&bid_id=" + bid_id + "&supplier_id=" + supplier_id + "&j=" + j + "&i=" + i + "&k=" + k,
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

        function createPurchaseOrder(id, bid_id, supplier_id, j, i, k)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/create-purchase-order']).'?id=" + id + "&bid_id=" + bid_id + "&supplier_id=" + supplier_id + "&j=" + j + "&i=" + i + "&k=" + k,
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

        function createContract(id, bid_id, supplier_id, j, i, k)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/create-contract']).'?id=" + id + "&bid_id=" + bid_id + "&supplier_id=" + supplier_id + "&j=" + j + "&i=" + i + "&k=" + k,
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