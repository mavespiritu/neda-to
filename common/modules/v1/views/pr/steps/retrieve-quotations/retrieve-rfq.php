<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
?>

<h4>4.2 Retrieve Supplier Quotation</h4>
<p><i class="fa fa-exclamation-circle"></i> Input prices from supplier quotations.</p>
<div class="pull-right">
    <?= Html::button('Retrieve Quotation', ['value' => Url::to(['/v1/pr/retrieve-rfq-quotation', 'id' => $model->id]), 'class' => 'btn btn-success', 'id' => 'retrieve-rfq-button']) ?>
</div>
<div class="clearfix"></div>
<h4>Supplier List</h4>
<table class="table table-bordered table-responsive table-condensed table-striped table-hover">
    <thead>
        <tr>
            <th style="width: 15%;">RFQ No.</th>
            <th>Supplier Name</th>
            <th>Business Address</th>
            <th>Date Retrieved</th>
            <td align=right><b>ABC</b></td>
            <th style="width: 20%;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    <?php if($rfqs){ ?>
        <?php foreach($rfqs as $rfq){ ?>
            <tr>
                <td><b><?= Html::a($rfq->rfq_no, null, ['href' => 'javascript:void(0)', 'onclick' => 'loadRfq('.$rfq->id.')']) ?></b></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td align=right><b><?= number_format($model->rfqTotal, 2) ?></b></td>
                <td>&nbsp;</td>
            </tr>
            <?php if($rfq->suppliers){ ?>
                <?php foreach($rfq->suppliers as $supplier){ ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td><?= $supplier->business_name ?></td>
                        <td><?= $supplier->business_address ?></td>
                        <td><?= $rfq->getRfqInfo($supplier->id)->date_retrieved ?></td>
                        <td align=right><?= number_format($rfq->getRfqInfoTotal($supplier->id), 2) ?></td>
                        <td align=right>
                            <?= Html::button('<i class="fa fa-edit"></i> Edit', ['value' => Url::to(['/v1/pr/update-rfq-quotation', 'id' => $model->id, 'rfq_id' => $rfq->id, 'supplier_id' => $supplier->id]), 'class' => 'btn btn-xs btn-warning update-rfq-quotation-button']) ?>
                            <?= Html::button('<i class="fa fa-trash"></i> Delete', ['onclick' => 'deleteRfqInfo('.$rfq->id.','.$supplier->id.')', 'class' => 'btn btn-xs btn-danger']) ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    </tbody>
</table>
<div id="retrieve-rfq-content"></div>
<?php
  Modal::begin([
    'id' => 'retrieve-rfq-modal',
    'size' => "modal-lg",
    'header' => '<div id="retrieve-rfq-modal-header"><h4>Retrieve Quotation</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="retrieve-rfq-modal-content"></div>';
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
        
        function deleteRfqInfo(rfq_id, supplier_id)
        {
            if(confirm("Are you sure you want to delete this item?"))
            {
                $.ajax({
                    url: "'.Url::to(['/v1/pr/delete-rfq-info']).'?rfq_id="+ rfq_id +"&supplier_id=" + supplier_id,
                    method: "post",
                    beforeSend: function(){
                        $("#pr-container").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                    },
                    success: function (data) {
                        console.log(this.data);
                        alert("Supplier has been removed");
                        rfqRetrieveQuotation(id);
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        }

        function loadRfq(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/view-rfq']).'?id="+ id,
                beforeSend: function(){
                    $("#rfq-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#retrieve-rfq-content").empty();
                    $("#retrieve-rfq-content").hide();
                    $("#retrieve-rfq-content").fadeIn("slow");
                    $("#retrieve-rfq-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            $("#retrieve-rfq-button").click(function(){
              $("#retrieve-rfq-modal").modal("show").find("#retrieve-rfq-modal-content").load($(this).attr("value"));
            });
            $(".update-rfq-quotation-button").click(function(){
                $("#retrieve-rfq-modal").modal("show").find("#retrieve-rfq-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>