<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<h4>9. Generate Reports</h4>
<ul class="products-list product-list-in-box navigation">
    <li class="item">
        <div class="product-img">
            <div class="circle">9.1</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="generatePr(<?= $model->id?>);" class="product-title">Purchase Request (PR)
            </a>
            <span class="product-description">Generate PR report</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">9.2</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="generateApr(<?= $model->id?>);" class="product-title">Agency Purchase Request (APR)
            </a>
            <span class="product-description">Generate APR report</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">9.3</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="generateRfq(<?= $model->id?>);" class="product-title">Request for Quotation (RFQ)
            </a>
            <span class="product-description">Generate RFQ report</span>
        </div>
    </li>
</ul>
<?php
    $script = '
        function groupAprItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/group-apr-items']).'?id=" + id,
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

        function groupRfqItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/group-rfq-items']).'?id=" + id,
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