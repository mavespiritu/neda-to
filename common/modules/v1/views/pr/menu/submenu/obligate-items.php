<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<h3 class="panel-title">9. ORS</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Obligate items procured and non-procurable items.</p>
<ul class="products-list product-list-in-box navigation">
<?php $i = 1; ?>
<?php if($pos){ ?>
    <?php foreach($pos as $po){ ?>
        <li class="item">
            <div class="product-img">
                <div class="circle">9.<?= $i ?></div>
            </div>
            <div class="product-info">
                <a href="javascript:void(0)" onclick="obligatePo('<?= $model->id ?>','<?= $po->id ?>','<?= $i ?>','PO');" class="product-title"><?= $po->type == 'PO' ? 'PO No. '.$po->pocnNo : 'Contract No. '.$po->pocnNo ?>
                <?= count($po->ors) > 0 ? '<span class="badge bg-green pull-right"><i class="fa fa-check"></i></span>' : '' ?>
                </a>
                <span class="product-description"><?= $po->supplier->business_name ?></span>
            </div>
        </li>
        <?php $i++ ?>
    <?php } ?>
<?php } ?>
<!-- <?php if($model->apr){ ?>
    <li class="item">
        <div class="product-img">
            <div class="circle">8.<?php //$i + 1 ?></div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="obligatePo('<?= $model->id ?>', '<?= $model->apr->id ?>' , '<?= $i + 1 ?>', 'APR');" class="product-title">APR Items
            <?= $model->orsOfApr > 0 ? '<span class="badge bg-green pull-right"><i class="fa fa-check"></i></span>' : '' ?>
            </a>
            <span class="product-description">Obligate items from APR</span>
        </div>
    </li>
<?php } ?> -->
    <li class="item">
        <div class="product-img">
            <div class="circle">9.<?= $i ?></div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="obligatePo('<?= $model->id ?>', null , '<?= $i ?>','NP');" class="product-title">Non-procurable Items
            <?= $model->orsWithoutPo > 0 ? '<span class="badge bg-green pull-right"><i class="fa fa-check"></i></span>' : '' ?>
            </a>
            <span class="product-description">Obligate non-procurable items</span>
        </div>
    </li>
</ul>
<?php
    $script = '
        function obligatePo(id, po_id, i, type)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/obligate-item']).'?id=" + id + "&po_id=" + po_id + "&i=" + i+ "&type=" + type,
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