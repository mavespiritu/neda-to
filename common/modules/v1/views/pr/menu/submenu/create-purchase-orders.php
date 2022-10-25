<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<h4>6. Create Purchase Order/Contract</h4>
<ul class="products-list product-list-in-box navigation">
<?php if($suppliers){ ?>
    <?php $i = 1; ?>
    <?php foreach($suppliers as $supplier){ ?>
        <li class="item">
            <div class="product-img">
                <div class="circle">6.<?= $i ?></div>
            </div>
            <div class="product-info">
                <a href="javascript:void(0)" onclick="selectWinningSupplier('<?= $model->id ?>','<?= $supplier->id ?>','<?= $i ?>');" class="product-title"><?= $supplier->business_name ?>
                </a>
                <span class="product-description"><?= $supplier->business_address ?></span>
            </div>
        </li>
        <?php $i++ ?>
    <?php } ?>
<?php }else{ ?>
    <li class="item">
        No bidding conducted. Click <a href="javascript:void(0)" onclick="bidItems(<?= $model->id ?>);" >here</a> and accomplish Step 5.
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