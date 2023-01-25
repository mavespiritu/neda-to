<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<h3 class="panel-title">3. RFQs/APRs</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Request quotations from suppliers.</p>
<ul class="products-list product-list-in-box navigation">
    <li class="item">
        <div class="product-img">
            <div class="circle">3.1</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="aprQuotation(<?= $model->id?>);" class="product-title">Request APR
            <?= $model->aprCount > 0 ? '<span class="badge bg-green pull-right"><i class="fa fa-check"></i></span>' : '' ?>
            </a>
            <span class="product-description">Generate APR for PS-DBM</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">3.2</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="rfqQuotation(<?= $model->id?>);" class="product-title">Request RFQ
            <?= $model->rfqCount > 0 ? '<span class="badge bg-green pull-right"><i class="fa fa-check"></i></span>' : '' ?>
            </a>
            <span class="product-description">Generate RFQ for outside suppliers.</span>
        </div>
    </li>
</ul>
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

        function rfqQuotation(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/request-rfq']).'?id=" + id,
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