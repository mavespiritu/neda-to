<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use  yii\web\View;
?>

<div class="row">
    <div class="col-md-6 col-xs-12">
    <h4>PR Details</h4>
    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table no-border table-hover'],
        'attributes' => [
            'pr_no',
            'officeName',
            [
                'attribute' => 'type',
                'format' => 'raw',
                'value' => function($model){
                    return $model->type == 'Supply' ? 'Goods' : 'Service/Contract';
                }
            ],
            'year',
            'procurementModeName',
            'fundSourceName',
            'fundClusterName',
            'purpose:ntext',
            'requesterName',
            'date_requested',
            'approverName',
            'date_approved',
            'creatorName',
            'date_created',
        ],
    ]) ?>
    </div>
    <div class="col-md-6 col-xs-12">
        <h4>Reports Available</h4>
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
                <?= $po->type == 'PO' ? Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;PO No. '.$po->pocnNo, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printPo("'.$model->id.'","'.$po->bid_id.'","'.$po->supplier_id.'")']) : Html::button('<i class="fa fa-file-o"></i>&nbsp;&nbsp;&nbsp;Contract No. '.$po->pocnNo, ['class' => 'btn btn-default btn-xs report-button', 'onclick' => 'printPo('.$po->id.')'])?>
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

        function printPo(id, bid_id, supplier_id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-po']).'?id=" + id + "&bid_id=" + bid_id +"&supplier_id=" + supplier_id, 
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