<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<h3 class="panel-title">5. AOQ</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Canvas/bid items to select winning suppliers.</p>
<ul class="products-list product-list-in-box navigation">
<?php if($rfqs){ ?>
    <?php $i = 1; ?>
    <?php foreach($rfqs as $rfq){ ?>
        <li class="item">
            <div class="product-img">
                <div class="circle">5.<?= $i ?></div>
            </div>
            <div class="product-info">
                <a href="javascript:void(0)" onclick="bidRfq('<?= $model->id?>','<?= $rfq->id?>','<?= $i ?>');" class="product-title">RFQ. No. <?= $rfq->rfq_no ?>
                </a>
                <span class="product-description">Bid the selected RFQ.</span>
            </div>
        </li>
        <?php $i++ ?>
    <?php } ?>
<?php }else{ ?>
    <li class="item">
        No requested quotation. Click <a href="javascript:void(0)" onclick="setQuotations(<?= $model->id?>);" >here</a> and accomplish Step 3.2.
    </li>
<?php } ?>
</ul>
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