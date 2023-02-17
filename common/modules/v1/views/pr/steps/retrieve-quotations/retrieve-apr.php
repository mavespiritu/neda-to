<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
?>

<h3 class="panel-title">3.3 Retrieve APR</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Input prices from retrieved APR.</p>

<?php if($model->apr && $model->apr->date_prepared != ''){ ?>
    <div id="agency-procurement-price"></div>
<?php }else{ ?>
<p class="text-center">No requested APR. Please request <a href="javascript:void(0)" onclick="aprQuotation(<?= $model->id ?>)">here</a>.</p>
<?php } ?>
<?php
    $script = '
        function loadAgencyQuotationForm(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/apr-quotation']).'?id=" + id,
                beforeSend: function(){
                    $("#agency-procurement-price").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#agency-procurement-price").empty();
                    $("#agency-procurement-price").hide();
                    $("#agency-procurement-price").fadeIn("slow");
                    $("#agency-procurement-price").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            loadAgencyQuotationForm('.$model->id.');
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>