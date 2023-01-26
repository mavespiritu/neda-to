<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use  yii\web\View;
?>

<div class="row">
    <div class="col-md-8 col-xs-12">
        <table class="table table-condensed table-responsive table-hover">
            <tr>
                <th style="width: 25%;">PR No.</th>
                <td style="width: 25%;"><?= $model->pr_no ?></td>
                <th style="width: 25%;">Year</th>
                <td style="width: 25%;"><?= $model->year ?></td>
            </tr>
            <tr>
                <th>Purpose</th>
                <td colspan=3><?= $model->purpose ?></td>
            </tr>
            <tr>
                <th>Division</th>
                <td><?= $model->officeName ?></td>
                <th>Type</th>
                <td><?= $model->type == 'Supply' ? 'Goods' : 'Service/Contract' ?></td>
            </tr>
            <tr>
                <th>Fund Source</th>
                <td><?= $model->fundSourceName ?></td>
                <th>Fund Cluster</th>
                <td><?= $model->fundClusterName ?></td>
            </tr>
            <tr>
                <th>Mode of Procurement</th>
                <td colspan=3><?= $model->procurementModeName ?></td>
            </tr>
            <tr>
                <th>Requested By</th>
                <td><?= $model->requesterName ?></td>
                <th>Date Requested</th>
                <td><?= $model->date_requested ?></td>
            </tr>
            <tr>
                <th>Approved By</th>
                <td><?= $model->approverName ?></td>
                <th>Date Approved</th>
                <td><?= $model->date_approved ?></td>
            </tr>
            <tr>
                <th>Created By</th>
                <td><?= $model->creatorName ?></td>
                <th>Date Created</th>
                <td><?= $model->date_created ?></td>
            </tr>
            <tr>
                <th>ABC</th>
                <td colspan=3><?= number_format($model->total, 2) ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-4 col-xs-12">
        <h3 class="panel-title"><i class="fa fa-file-o"></i> Reports Available</h3>
        <br>
        <?= Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;PR No. '.$model->pr_no, ['value' => Url::to(['/v1/pr/pr', 'id' => $model->id]), 'class' => 'btn btn-default btn-xs report-button', 'id' => 'pr-button']) ?>
        <?= Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;APR No. '.$model->pr_no, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printApr('.$model->id.')']) ?>
        <?php if($rfqs){ ?>
            <?php foreach($rfqs as $rfq){ ?>
                <?= Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;RFQ No. '.$rfq->rfq_no, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printRfq('.$rfq->id.')']) ?>
                <?php if($rfq->suppliers){ ?>
                    <?php foreach($rfq->suppliers as $supplier){ ?>
                        <?= Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;RFQ No. '.$rfq->rfq_no.': '.$supplier->business_name, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printRfqInfo('.$model->id.','.$rfq->id.','.$supplier->id.')']) ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
        <?php if($bids){ ?>
            <?php foreach($bids as $bid){ ?>
                <?= Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;AOQ No. '.$bid->bid_no, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printAoq('.$bid->id.')']) ?>
            <?php } ?>
        <?php } ?>
        <?php if($pos){ ?>
            <?php foreach($pos as $po){ ?>
                <?= $po->type == 'PO' ? Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;PO No. '.$po->pocnNo, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printPo("'.$model->id.'","'.$po->bid_id.'","'.$po->supplier_id.'","'.$po->type.'")']) : Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;Contract No. '.$po->pocnNo, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printPo("'.$model->id.'","'.$po->bid_id.'","'.$po->supplier_id.'","'.$po->type.'")'])?>
            <?php } ?>
        <?php } ?>
        <?php if($noas){ ?>
            <?php foreach($noas as $noa){ ?>
                <?= Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;NOA No. '.$noa->po->pocnNo, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printNoa('.$model->id.','.$noa->po->id.')']) ?>
            <?php } ?>
        <?php } ?>
        <?php if($ntps){ ?>
            <?php foreach($ntps as $ntp){ ?>
                <?= Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;NTP No. '.$ntp->po->pocnNo, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printNtp('.$model->id.','.$ntp->po->id.')']) ?>
            <?php } ?>
        <?php } ?>
        <?php if($iars){ ?>
            <?php foreach($iars as $iar){ ?>
                <?= Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;IAR No. '.$iar->iar_no, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printIar('.$iar->id.')']) ?>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-xs-12">
        <h3 class="panel-title"> <i class="fa fa-list"></i> Included Items</h3>
        <br>
        <div id="pr-items"></div>
    </div>
</div>
<style>
    .report-button{
        margin: 5px 5px;
    }
</style>
<?php
  Modal::begin([
    'id' => 'pr-modal',
    'size' => "modal-xl",
    'header' => '<div id="pr-modal-header"><h4>Purchase Request (PR)</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="pr-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(document).ready(function(){
            prItems('.$model->id.');
        });    

        function prItems(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/select-pr-items']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-items").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-items").empty();
                    $("#pr-items").hide();
                    $("#pr-items").fadeIn("slow");
                    $("#pr-items").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        function printApr(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-apr']).'?id=" + id, 
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

        function printRfqInfo(id, rfq_id, supplier_id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-rfq-info']).'?rfq_id="+ rfq_id +"&supplier_id=" + supplier_id, 
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

        function printAoq(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-aoq']).'?id=" + id, 
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

        function printPo(id, bid_id, supplier_id, type)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-po']).'?id=" + id + "&bid_id=" + bid_id +"&supplier_id=" + supplier_id +"&type=" + type, 
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

        function printNoa(id, po_id)
        {
            var printWindow = window.open(
            "'.Url::to(['/v1/pr/print-noa']).'?id=" + id + "&po_id=" + po_id, 
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

        function printNtp(id, po_id)
        {
            var printWindow = window.open(
            "'.Url::to(['/v1/pr/print-ntp']).'?id="+ id +"&po_id=" + po_id,
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

        $("#pr-button").click(function(){
            $("#pr-modal").modal("show").find("#pr-modal-content").load($(this).attr("value"));
        });

        function printIar(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-iar']).'?id=" + id, 
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
    ';

    $this->registerJs($script, View::POS_END);
?>