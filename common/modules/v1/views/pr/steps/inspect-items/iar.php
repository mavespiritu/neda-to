<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use frontend\assets\AppAsset;

$asset = AppAsset::register($this);
?>

<h4>IAR No. <?= $iar->iar_no ?></h4>
<div class="iar-view">
    <h4 class="text-center"><b>Inspection and Acceptance Report</b></h4>
    <br>
    <br>
    <table class="table table-bordered">
        <tr>
            <td><b>Entity Name:  </b><u><?= $entity->value ?></u></td>
            <td><b>Fund Cluster:  </b><u><?= $model->fundCluster->title ?></u></td>
        </tr>
        <tr>
            <td>
                <b>Supplier:  </b><u><?= $supplier->business_name ?></u>
                <br>
                <b>Purchase/Contract No.:  </b><u><?= $po->pocnNo ?></u>&nbsp;&nbsp;&nbsp;&nbsp;<b>Date:  </b><u><?= date("F j, Y", strtotime($po->po_date)) ?></u>
                <br>
                <b>Requesitioning Office/Dept.:  </b><u><?= $model->officeName ?></u>
                <br>
                <b>Responsibility Center Code:  </b><u><?= implode(', ', $rccs) ?></u>
            </td>
            <td>
                <b>IAR No.:  </b><u><?= $iar->iar_no ?></u>
                <br>
                <b>Date:  </b><u><?= date("F j, Y", strtotime($iar->iar_date)) ?></u>
                <br>
                <b>Invoice No.:  </b><u><?= $iar->invoice_no ?></u>
                <br>
                <b>Invoice Date:  </b><u><?= date("F j, Y", strtotime($iar->invoice_date)) ?></u>
            </td>
        </tr>
    </table>
    <table class="table table-bordered table-striped table-hover table-condensed table-responsive">
        <thead>
            <tr>
                <td align=center><b>Stock/Property No.</b></td>
                <td align=center><b>Description</b></td>
                <td align=center><b>Unit</b></td>
                <td align=center><b>Qty</b></td>
                <td align=center><b>Delivery Time</b></td>
                <td align=center><b>Courtesy of <br> Delivery Staff</b></td>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($items)){ ?>
                <?php foreach($items as $item){ ?>
                    <?php $id = $item['id']; ?>
                    <tr>
                        <td align=center><?= $item['id'] ?></td>
                        <td><?= $item['item'] ?></td>
                        <td><?= $item['unit'] ?></td>
                        <td align=center><?= number_format($item['delivered'], 0) ?></td>
                        <td align=center><?= $item['delivery_time'] ?></td>
                        <td align=center><?= $item['courtesy'] ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <tr>
                <td>&nbsp;</td>
                <td align=center><b>xxxxxx NOTHING FOLLOWS xxxxxx</b></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan=4 align=center><b><i>INSPECTION</i></b></td>
                <td colspan=3 align=center><b><i>ACCEPTANCE</i></b></td>
            </tr>
            <tr>
                <td colspan=4>
                    <b>Date Inspected:  </b><u><?= date("F j, Y", strtotime($iar->date_inspected)) ?></u>
                    <br>
                    <br>
                    <input type="checkbox" checked /> Inspected, verified and found in order as to quantity and specifications.
                    <br>
                    <br>
                    <br>
                    <p class="text-center">
                        <b><u><?= strtoupper($iar->inspectorName) ?></u></b>
                        <br>
                        Inspection Officer/Inspection Committee
                    </p>
                </td>
                <td colspan=3>
                    <b>Date Received:  </b><u><?= date("F j, Y", strtotime($iar->date_received)) ?></u>
                    <br>
                    <input type="checkbox" <?= $iar->status == 'Complete' ? 'checked' : '' ?> /> Complete 
                    <br>
                    <input type="checkbox" <?= $iar->status == 'Partial' ? 'checked' : '' ?>  /> Partial (pls. specify quantity)
                    <br>
                    <br>
                    <br>
                    <p class="text-center">
                        <b><u><?= strtoupper($iar->receiverName) ?></u></b>
                        <br>
                        Supply and/or Property Custodian
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php
    $script = '
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