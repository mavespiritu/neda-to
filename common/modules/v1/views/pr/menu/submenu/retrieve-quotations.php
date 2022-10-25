<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

<h4>4. Retrieve Quotation</h4>
<ul class="products-list product-list-in-box navigation">
    <li class="item">
        <div class="product-img">
            <div class="circle">4.1</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="aprRetrieveQuotation(<?= $model->id?>);" class="product-title">Agency Procurement Quotation
            </a>
            <span class="product-description">Retrieve quotation from agency procurement</span>
        </div>
    </li>
    <li class="item">
        <div class="product-img">
            <div class="circle">4.2</div>
        </div>
        <div class="product-info">
            <a href="javascript:void(0)" onclick="rfqRetrieveQuotation(<?= $model->id?>);" class="product-title">Supplier Quotation
            </a>
            <span class="product-description">Retrieve quotation from outside suppliers.</span>
        </div>
    </li>
</ul>
<?php
    $script = '
        function aprRetrieveQuotation(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/retrieve-apr']).'?id=" + id,
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

        function rfqRetrieveQuotation(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/retrieve-rfq']).'?id=" + id,
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