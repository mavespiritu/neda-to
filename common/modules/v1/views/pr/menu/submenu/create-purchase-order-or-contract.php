<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<h3 class="panel-title">7. POs/Contracts</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Generate purchase orders and contracts for winning suppliers.</p>
<ul class="products-list product-list-in-box navigation">
<?php if(!empty($data)){ ?>
    <?php $i = 1; ?>
    <?php foreach($data as $datum){ ?>
        <li><h5>AOQ No. <?= $datum['bid']->bid_no ?></h5></li>
        <?php if(!empty($datum['suppliers'])){ ?>
            <?php foreach($datum['suppliers'] as $supplier){ ?>
                <li class="item">
                    <div class="product-img">
                        <div class="circle">7.<?= $i ?></div>
                    </div>
                    <div class="product-info">
                        <a href="javascript:void(0)" onclick="selectType('<?= $model->id ?>','<?= $datum['bid']->id ?>','<?= $supplier->id ?>','<?= $i ?>');" class="product-title"><?= $supplier->business_name ?>
                        </a>
                        <span class="product-description"><?= $supplier->business_address ?></span>
                    </div>
                </li>
                <?php $i++ ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
<?php }else{ ?>
    <li class="item">
        No bidding conducted. Click <a href="javascript:void(0)" onclick="bidItems(<?= $model->id ?>);" >here</a> and accomplish Step 5.
    </li>
<?php } ?>
</ul>
<?php
    $script = '
        function selectType(id, bid_id, supplier_id, i)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/select-type']).'?id=" + id + "&bid_id=" + bid_id + "&supplier_id=" + supplier_id + "&i=" + i,
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