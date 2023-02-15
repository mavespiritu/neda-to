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

<h3 class="panel-title">5.<?= $i ?> AOQ for RFQ No. <?= $rfq->rfq_no ?></h3>
<br>
<h3 class="panel-title">AOQ Information
    <span class="pull-right">
    <?= $bid ? Html::a('<i class="fa fa-print"></i> Print', null, ['class' => 'btn btn-sm btn-info', 'onclick' => 'printAoq('.$bid->id.')']) : '' ?>
    <?= $bid ? Html::button('<i class="fa fa-edit"></i> Edit', ['value' => Url::to(['/v1/pr/update-bid', 'id' => $bid->id, 'i' => $i]), 'class' => 'btn btn-sm btn-warning', 'id' => 'update-bid-button']).' '.
               Html::a('<i class="fa fa-trash"></i> Delete', null, ['href' => 'javascript:void(0)', 'class' => 'btn btn-danger btn-sm delete-bid-button', 'onClick' => 'deleteBid('.$bid->id.')', 'data' => [
                    'confirm' => 'Are you sure you want to delete this bid?',
                    'method' => 'post',
                ],]).'</div>'
    :
    Html::button('Create AOQ', ['value' => Url::to(['/v1/pr/create-bid', 'id' => $model->id, 'rfq_id' => $rfq->id, 'i' => $i]), 'class' => 'btn btn-success btn-sm', 'id' => 'create-bid-button']) ?>
    </span>
</h3>
<p><i class="fa fa-exclamation-circle"></i> Create AOQ to enable selection of winning bidders.</p>
<br>
<table class="table table-bordered table-responsive table-condensed">
    <tbody>
        <tr>
            <td align=right style="width: 20%;"><b>Canvas/Bid No.</b></td>
            <td style="width: 30%;"><?= $bid ? $bid->bid_no : '&nbsp;' ?></td>
            <td align=right style="width: 20%;"><b>Date and Time of Opening</b></td>
            <td style="width: 30%;"><?= $bid ? date("F j, Y", strtotime($bid->date_opened)) : '&nbsp;' ?> <?= $bid ? $bid->time_opened : '&nbsp;' ?></td>
        </tr>
        <tr>
            <td align=right><b>BAC Chairperson</b></td>
            <td><?= $bid ? $bid->chairperson : '&nbsp;' ?></td>
            <td align=right><b>BAC Vice-Chairperson</b></td>
            <td><?= $bid ? $bid->viceChairperson : '&nbsp;' ?></td>
        </tr>
        <tr>
            <td align=right><b>Member</b></td>
            <td><?= $bid ? $bid->member : '&nbsp;' ?></td>
            <td align=right><b>Provisional Member with Technical Expertise</b></td>
            <td><?= $bid ? $bid->expert : '&nbsp;' ?></td>
        </tr>
        <tr>
            <td align=right><b>Provisional Member - End User (<?= $model->officeName ?>)</b></td>
            <td><?= $bid ? $bid->endUser : '&nbsp;' ?></td>
            <td align=right>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </tbody>
</table>
<br>
<h3 class="panel-title">Bid Items <span class="pull-right"><?= $bid ? Html::button('<i class="fa fa-gavel"></i> Select Winners', ['value' => Url::to(['/v1/pr/select-winner', 'id' => $bid->id, 'i' => $i]), 'class' => 'btn btn-sm btn-success winner-button']).' ' : ' ' ?></span></h3>
<p><i class="fa fa-exclamation-circle"></i> Choose winning bidders in each item.</p>
<br>
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
                <td colspan=<?= count($suppliers) * 2 ?> align=center><b>Participating Establishments</b></td>
            <?php } ?>
            <td rowspan=3 align=center style="width: 10%;"><b>Justification</b></td>
            <td rowspan=3 align=center style="width: 10%;"><b>Awarded to</b></td>
        </tr>
        <tr>
            <?php if($suppliers){ ?>
                <?php foreach($suppliers as $idx => $supplier){ ?>
                    <?php $totals[$supplier->id] = 0; ?>
                    <td align=center><b><?= $letters[$idx] ?><br><?= $supplier->business_name ?></b></td>
                    <td align=center><b>Specifications</b></td>
                <?php } ?>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
    <?php if(!empty($rfqItems)){ ?>
        <?php $j = 1; ?>
        <?php foreach($rfqItems as $rfqItem){ ?>
            <?php if($j == 1){ ?>
                <tr>
                    <td align=center><?= $j ?></td>
                    <td><?= $rfqItem['item'] ?></td>
                    <td align=center><?= $rfqItem['unit'] ?></td>
                    <td align=center><?= number_format($rfqItem['total'], 0) ?></td>
                    <td align=right><?= number_format($rfqItem['abc'], 2) ?></td>
                    <td align=right><b><?= number_format($rfqItem['total'] * $rfqItem['abc'], 2) ?></b></td>
                    <?php if($suppliers){ ?>
                        <?php foreach($suppliers as $supplier){ ?>
                            <td align=right style="width: 15%; background-color: <?= isset($winners[$rfqItem['id']][$supplier->id]) ? $winners[$rfqItem['id']][$supplier->id]['status'] == 'Awarded' ? 'yellow' : 'transparent' : 'transparent' ?>"><b><?= isset($costs[$rfqItem['id']][$supplier->id]['cost']) ? $costs[$rfqItem['id']][$supplier->id]['cost'] > 0 ? number_format($rfqItem['total'] * $costs[$rfqItem['id']][$supplier->id]['cost'], 2) : '-' : '-' ?></b></td>
                            <td><?= $costs[$rfqItem['id']][$supplier->id]['specification'] ?></td>
                            <?php $totals[$supplier->id] += isset($costs[$rfqItem['id']][$supplier->id]['cost']) ? $rfqItem['total'] * $costs[$rfqItem['id']][$supplier->id]['cost'] : 0; ?>
                        <?php } ?>
                    <?php } ?>
                    <td rowspan=<?= count($rfqItems) ?>><?= $bid ? $bid->justification : '' ?></td>
                    <td rowspan=<?= count($rfqItems) ?>><?= $bid ? $bid->recommendation : '' ?></td>
                    <?php $abcTotal += $rfqItem['total'] * $rfqItem['cost'] ?>
                </tr>
            <?php }else{ ?>
                <tr>
                    <td align=center><?= $j ?></td>
                    <td><?= $rfqItem['item'] ?></td>
                    <td align=center><?= $rfqItem['unit'] ?></td>
                    <td align=center><?= number_format($rfqItem['total'], 0) ?></td>
                    <td align=right><?= number_format($rfqItem['abc'], 2) ?></td>
                    <td align=right><b><?= number_format($rfqItem['total'] * $rfqItem['abc'], 2) ?></b></td>
                    <?php if($suppliers){ ?>
                        <?php foreach($suppliers as $supplier){ ?>
                            <td align=right style="width: 15%; background-color: <?= isset($winners[$rfqItem['id']][$supplier->id]) ? $winners[$rfqItem['id']][$supplier->id]['status'] == 'Awarded' ? 'yellow' : 'transparent' : 'transparent' ?>"><b><?= isset($costs[$rfqItem['id']][$supplier->id]['cost']) ? $costs[$rfqItem['id']][$supplier->id]['cost'] > 0 ? number_format($rfqItem['total'] * $costs[$rfqItem['id']][$supplier->id]['cost'], 2) : '-' : '-' ?></b></td>
                            <td><?= $costs[$rfqItem['id']][$supplier->id]['specification'] ?></td>
                            <?php $totals[$supplier->id] += isset($costs[$rfqItem['id']][$supplier->id]['cost']) ? $rfqItem['total'] * $costs[$rfqItem['id']][$supplier->id]['cost'] : 0; ?>
                        <?php } ?>
                    <?php } ?>
                    <?php $abcTotal += $rfqItem['total'] * $rfqItem['cost'] ?>
                </tr>
            <?php } ?>
            <?php $j++; ?>
        <?php } ?>
    <?php } ?>
    <tr>
        <td colspan=5 align=right><b>Total ABC</b></td>
        <td align=right><b><?= number_format($abcTotal, 2) ?></b></td>
        <?php if($suppliers){ ?>
            <?php foreach($suppliers as $supplier){ ?>
                <td align=right><b><?= number_format($totals[$supplier->id], 2) ?></b></td>
                <td>&nbsp;</td>
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
    'header' => '<div id="create-bid-modal-header"><h4>Create AOQ</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-bid-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-bid-modal',
    'size' => "modal-lg",
    'header' => '<div id="update-bid-modal-header"><h4>Edit AOQ</h4></div>',
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