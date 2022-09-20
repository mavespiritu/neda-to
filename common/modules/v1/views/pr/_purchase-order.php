<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
use yii\bootstrap\Collapse;
?>

<div class="box box-primary">
    <div class="box-header panel-title"><i class="fa fa-list"></i>Purchase Order List</div>
    <div class="box-body">
        <p><i class="fa fa-exclamation-circle"></i> Generate PO for each suppliers.</p>
        <?php if(!empty($rfqs)){ ?>
            <?php foreach($rfqs as $rfq){ ?>
                <?php $abcTotal[$rfq->id] = 0; ?>
                <div class="panel panel-info">
                    <div class="panel-heading"><h4 class="panel-title">RFQ No. <?= $rfq->rfq_no ?></h4></div>
                    <div class="panel-body">
                        
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<?php
  Modal::begin([
    'id' => 'generate-po-modal',
    'size' => "modal-lg",
    'header' => '<div id="generate-rfq-modal-header"><h4>Generate PO</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="generate-po-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function printRfq(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-rfq']).'?id=" + id, 
                "Print",
                "left=200", 
                "top=200", 
                "width=650", 
                "height=500", 
                "toolbar=0", 
                "resizable=0"
                );
                printWindow.addEventListener("load", function() {
                    printWindow.print();
                    setTimeout(function() {
                    printWindow.close();
                }, 1);
                }, true);
        }
        
        function deleteRfq(id, rfq_id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/delete-rfq']).'?id="+ id +"&rfq_id=" + rfq_id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    alert("RFQ has been deleted");
                    quotations(id);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function loadRfq(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/view-rfq']).'?id="+ id,
                beforeSend: function(){
                    $("#rfq-content-"+id).html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#rfq-content-"+id).empty();
                    $("#rfq-content-"+id).hide();
                    $("#rfq-content-"+id).fadeIn("slow");
                    $("#rfq-content-"+id).html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            $("#generate-po-button").click(function(){
              $("#generate-po-modal-content").html("");
              $("#generate-po-modal").modal("show").find("#generate-po-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>