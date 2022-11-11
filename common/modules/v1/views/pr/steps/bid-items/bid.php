<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;

$letters = range('A', 'Z');
$abcTotal = 0;
$totals = [];
?>

<h4>5.<?= $i ?> Canvas/Bid RFQ No. <?= $rfq->rfq_no ?></h4>
<p><i class="fa fa-exclamation-circle"></i> To accomplish the step, create a bid and proceed to the selection of winning bidders.</p>

    <?= $bid ? '<div class="pull-right">'.Html::button('<i class="fa fa-edit"></i> Edit Canvas/Bid', ['value' => Url::to(['/v1/pr/update-bid', 'id' => $bid->id, 'i' => $i]), 'class' => 'btn btn-warning', 'id' => 'update-bid-button']).' '.
               Html::a('<i class="fa fa-trash"></i> Delete Canvas/Bid', null, ['href' => 'javascript:void(0)', 'class' => 'btn btn-danger delete-bid-button', 'onClick' => 'deleteBid('.$bid->id.')', 'data' => [
                    'confirm' => 'Are you sure you want to delete this bid?',
                    'method' => 'post',
                ],]).'</div>'
    :
    Html::button('<i class="fa fa-legal"></i> Create Canvass/Bid', ['value' => Url::to(['/v1/pr/create-bid', 'id' => $model->id, 'rfq_id' => $rfq->id, 'i' => $i]), 'class' => 'btn btn-app', 'id' => 'create-bid-button']) ?>
</div>
<div class="clearfix"></div>
<h4>Canvass/Bid Information</h4>
<table class="table table-bordered table-responsive table-condensed">
    <tbody>
        <tr>
            <td align=right style="width: 20%;"><b>Canvass/Bid No.</b></td>
            <td><?= $bid ? $bid->bid_no : '&nbsp;' ?></td>
            <td align=right style="width: 20%;"><b>Date and Time of Opening</b></td>
            <td><?= $bid ? date("F j, Y", strtotime($bid->date_opened)) : '&nbsp;' ?> <?= $bid ? $bid->time_opened : '&nbsp;' ?></td>
        </tr>
        <tr>
            <td align=right style="width: 20%;"><b>BAC Chairperson</b></td>
            <td><?= $bid ? $bid->chairperson : '&nbsp;' ?></td>
            <td align=right style="width: 20%;"><b>BAC Vice-Chairperson</b></td>
            <td><?= $bid ? $bid->viceChairperson : '&nbsp;' ?></td>
        </tr>
        <tr>
            <td align=right style="width: 20%;"><b>Member</b></td>
            <td><?= $bid ? $bid->member : '&nbsp;' ?></td>
            <td align=right style="width: 20%;"><b>Provisional Member with Technical Expertise</b></td>
            <td><?= $bid ? $bid->expert : '&nbsp;' ?></td>
        </tr>
        <tr>
            <td align=right style="width: 20%;"><b>Provisional Member - End User (<?= $model->officeName ?>)</b></td>
            <td><?= $bid ? $bid->endUser : '&nbsp;' ?></td>
            <td align=right style="width: 20%;">&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </tbody>
</table>
<p><i class="fa fa-exclamation-circle"></i> Create canvas/bid to enable selection of winners.</p>
<?= $bid ? Html::button('<i class="fa fa-gavel"></i> Select Winners', ['value' => Url::to(['/v1/pr/select-winner', 'id' => $bid->id, 'i' => $i]), 'class' => 'btn btn-app winner-button']) : '' ?>
<?= $bid ? Html::a('<i class="fa fa-print"></i> Print AOQ', null, ['class' => 'btn btn-app', 'onclick' => 'printAoq('.$model->id.')']) : '' ?>
<br>
<h4>Items</h4>
<table class="table table-bordered table-condensed table-striped table-hover table-responsive">
    <thead>
        <tr>
            <td rowspan=3 align=center><b>Item No.</b></td>
            <td rowspan=3 align=center><b>Nomenclature</b></td>
            <td rowspan=3 align=center><b>Unit of Measure</b></td>
            <td rowspan=3 align=center><b>Qty</b></td>
            <td rowspan=3 align=center><b>Unit Cost</b></td>
            <td rowspan=3 align=center><b>ABC</b></td>
            <?php if(!empty($suppliers)){ ?>
                <td colspan=<?= count($suppliers) ?> align=center><b>Participating Establishments</b></td>
            <?php } ?>
            <td rowspan=3 align=center><b>Awarded to</b></td>
            <td rowspan=3 align=center><b>Justification</b></td>
        </tr>
        <tr>
            <?php if($suppliers){ ?>
                <?php foreach($suppliers as $idx => $supplier){ ?>
                    <?php $totals[$supplier->id] = 0; ?>
                    <td align=center><b><?= $letters[$idx] ?>. <?= $supplier->business_name ?></b></td>
                <?php } ?>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
    <?php if(!empty($rfqItems)){ ?>
        <?php $j = 1; ?>
        <?php foreach($rfqItems as $rfqItem){ ?>
            <tr style="background-color: <?= isset($winners[$rfqItem['id']]) ? !empty($winners[$rfqItem['id']]['winner']) ? 'transparent' : '#EF4444' : 'transparent' ?>">
                <td align=center><?= $j ?></td>
                <td><?= $rfqItem['item'] ?></td>
                <td align=center><?= $rfqItem['unit'] ?></td>
                <td align=center><?= number_format($rfqItem['total'], 0) ?></td>
                <td align=right><?= number_format($rfqItem['cost'], 2) ?></td>
                <td align=right><b><?= number_format($rfqItem['total'] * $rfqItem['cost'], 2) ?></b></td>
                <?php if($suppliers){ ?>
                    <?php foreach($suppliers as $supplier){ ?>
                        <td align=right style="width: 15%; background-color: <?= isset($winners[$rfqItem['id']][$supplier->id]) ? $winners[$rfqItem['id']][$supplier->id]['status'] == 'Awarded' ? 'yellow' : 'transparent' : 'transparent' ?>"><b><?= isset($costs[$rfqItem['id']][$supplier->id]['cost']) ? $costs[$rfqItem['id']][$supplier->id]['cost'] > 0 ? number_format($rfqItem['total'] * $costs[$rfqItem['id']][$supplier->id]['cost'], 2) : '-' : '-' ?></b></td>
                        
                        <?php $totals[$supplier->id] += isset($costs[$rfqItem['id']][$supplier->id]['cost']) ? $rfqItem['total'] * $costs[$rfqItem['id']][$supplier->id]['cost'] : 0; ?>
                    <?php } ?>
                <?php } ?>
                <td><?= isset($winners[$rfqItem['id']]) ? !empty($winners[$rfqItem['id']]['winner']) ? $winners[$rfqItem['id']]['winner']->business_name : 'Failed' : '' ?></td>
                <td><?= isset($winners[$rfqItem['id']]['justification']) ? $winners[$rfqItem['id']]['justification'] : '' ?></td>
                <?php $abcTotal += $rfqItem['total'] * $rfqItem['cost'] ?>
            </tr>
            <?php $j++; ?>
        <?php } ?>
    <?php } ?>
    <tr>
        <td colspan=5 align=right><b>Total ABC</b></td>
        <td align=right><b><?= number_format($abcTotal, 2) ?></b></td>
        <?php if($suppliers){ ?>
            <?php foreach($suppliers as $supplier){ ?>
                <td align=right><b><?= number_format($totals[$supplier->id], 2) ?></b></td>
            <?php } ?>
        <?php } ?>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </tbody>
</table>

<?php
  Modal::begin([
    'id' => 'create-bid-modal',
    'size' => "modal-lg",
    'header' => '<div id="create-bid-modal-header"><h4>Create Canvas/Bid</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-bid-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-bid-modal',
    'size' => "modal-lg",
    'header' => '<div id="update-bid-modal-header"><h4>Edit Canvas/Bid</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-bid-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'winner-modal',
    'size' => "modal-xl",
    'header' => '<div id="winner-modal-header"><h4>Set Winning Bidders</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="winner-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function deleteBid(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/delete-bid']).'?id=" + id,
                success: function (data) {
                    console.log(this.data);
                    alert("Bid Information has been deleted");
                    bidRfq('.$model->id.', '.$rfq->id.', '.$i.');
                },
                error: function (err) {
                    console.log(err);
                }
            });
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

        $(document).ready(function(){
            $("#create-bid-button").click(function(){
              $("#create-bid-modal").modal("show").find("#create-bid-modal-content").load($(this).attr("value"));
            });
            $("#update-bid-button").click(function(){
                $("#update-bid-modal").modal("show").find("#update-bid-modal-content").load($(this).attr("value"));
            });
            $(".winner-button").click(function(){
                $("#winner-modal-content").html("");
                $("#winner-modal").modal("show").find("#winner-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>