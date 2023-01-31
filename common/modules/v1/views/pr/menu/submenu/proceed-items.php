<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<h3 class="panel-title">8. NTP</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Generate NTP to notify supplier.</p>
<ul class="products-list product-list-in-box navigation">
<?php if($pos){ ?>
    <?php $i = 1; ?>
    <?php foreach($pos as $po){ ?>
        <li class="item">
            <div class="product-img">
                <div class="circle">8.<?= $i ?></div>
            </div>
            <div class="product-info">
                <a href="javascript:void(0)" onclick="createNtp('<?= $model->id ?>','<?= $po->id ?>','<?= $i ?>');" class="product-title"><?= $po->type == 'PO' ? 'PO No. '.$po->pocnNo : 'Contract No. '.$po->pocnNo ?>
                <?= $po->ntps && $po->noas ? '<span class="badge bg-green pull-right"><i class="fa fa-check"></i></span>' : '' ?>
                </a>
                <span class="product-description"><?= $po->supplier->business_name ?></span>
            </div>
        </li>
        <?php $i++ ?>
    <?php } ?>
<?php }else{ ?>
    <li class="item">
        No purchase order created. Click <a href="javascript:void(0)" onclick="createPurchaseOrderOrContract(<?= $model->id ?>);" >here</a> and accomplish Step 6.
    </li>
<?php } ?>
</ul>
<?php
    $script = '
        function proceedItems(id, po_id, i)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/proceed-and-award-item']).'?id=" + id + "&po_id=" + po_id + "&i=" + i,
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

        function createNtp(id, po_id, i)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/create-ntp']).'?id=" + id + "&po_id=" + po_id + "&i=" + i,
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