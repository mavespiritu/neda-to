<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
use yii\bootstrap\Collapse;

$letters = range('A', 'Z');
$abcTotal = [];
$totals = [];
?>
<div class="box box-primary">
    <div class="box-header panel-title"><i class="fa fa-list"></i>Retrieved RFQ</div>
    <div class="box-body">
        <div class="pull-right">
            <?= Html::button('Retrieve RFQ', ['value' => Url::to(['/v1/pr/retrieve-rfq', 'id' => $model->id]), 'class' => 'btn btn-success', 'id' => 'retrieve-rfq-button']) ?>
        </div>
        <div class="clearfix"></div>
        <br>
        <?php if(!empty($rfqs)){ ?>
            <?php foreach($rfqs as $rfq){ ?>
                <?php $abcTotal[$rfq->id] = 0; ?>
                <div class="panel panel-info">
                    <div class="panel-heading"><h4 class="panel-title">RFQ No. <?= $rfq->rfq_no ?></h4></div>
                    <div class="panel-body">
                        <p><i class="fa fa-exclamation-circle"></i> Retrieve the RFQ to enable bidding. Click the "Retrieve RFQ" button above.</p>
                        <?php if(!empty($suppliers[$rfq->id])){ ?>
                        <p><b>Bid Details</b>
                        <span class="pull-right">
                        <?= empty($bids[$rfq->id]) ? Html::button('Set Bidding', ['value' => Url::to(['/v1/pr/bid', 'id' => $model->id, 'rfq_id' => $rfq->id]), 'class' => 'btn btn-success btn-sm bid-button']) : 
                        Html::button('Edit Bidding', ['value' => Url::to(['/v1/pr/update-bid', 'id' => $bids[$rfq->id]->id]), 'class' => 'btn btn-primary btn-sm update-bid-button']).' '.
                        Html::a('Delete Bidding', null, ['href' => 'javascript:void(0)', 'class' => 'btn btn-danger btn-sm delete-bid-button', 'onClick' => 'deleteBid('.$bids[$rfq->id]->id.')', 'data' => [
                            'confirm' => 'Are you sure you want to delete this bid?',
                            'method' => 'post',
                        ],])
                        ?>
                        </span></p>
                        <br>
                        <table class="table table-bordered table-condensed table-responsive">
                        <tr>
                            <td style="width: 20%;" align=right><b>BID No.:</b></td>
                            <td style="width: 30%;"><?= $rfq->pr->pr_no.'-00' ?></td>
                            <td style="width: 20%;" align=right><b>Member:</b></td>
                            <td style="width: 30%;"><?= !empty($bids[$rfq->id]) ? $bids[$rfq->id]->member : '' ?></td>
                        </tr>
                        <tr>
                            <td align=right><b>Chairperson:</b></td>
                            <td><?= !empty($bids[$rfq->id]) ? $bids[$rfq->id]->chairperson : '' ?></td>
                            <td align=right><b>Provisional Member:<b/></td>
                            <td><?= !empty($bids[$rfq->id]) ? $bids[$rfq->id]->expert : '' ?></td>
                        </tr>
                        <tr>
                            <td align=right><b>Vice-Chairperson:</b></td>
                            <td><?= !empty($bids[$rfq->id]) ? $bids[$rfq->id]->viceChairperson : '' ?></td>
                            <td align=right><b>Provisional Member - End User:</b></td>
                            <td><?= !empty($bids[$rfq->id]) ? $bids[$rfq->id]->endUser : '' ?></td>
                        </tr>
                        <tr>
                            <td style="width: 20%;" align=right><b>Date of Opening:</b></td>
                            <td style="width: 30%;"><?= !empty($bids[$rfq->id]) ? $bids[$rfq->id]->date_opened : '' ?></td>
                            <td style="width: 20%;" align=right><b>Time of Opening:</b></td>
                            <td style="width: 30%;"><?= !empty($bids[$rfq->id]) ? $bids[$rfq->id]->time_opened : '' ?></td>
                        </tr>
                        </table>
                        <?php } ?>
                        <p><i class="fa fa-exclamation-circle"></i> Set bidding details first to enable setting of winners in items. Click the "Set Bidding" button above.</p>
                        <span class="pull-right"><?= !empty($bids[$rfq->id]) ? Html::button('Set Winners', ['value' => Url::to(['/v1/pr/winner', 'id' => $bids[$rfq->id]->id]), 'class' => 'btn btn-success btn-sm winner-button']) : '' ?></span>
                        <br>
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
                                    <?php if(!empty($suppliers[$rfq->id])){ ?>
                                        <td colspan=<?= count($suppliers[$rfq->id]) ?> align=center><b>Participating Establishments</b></td>
                                    <?php } ?>
                                    <td rowspan=3 align=center><b>Awarded to</b></td>
                                    <td rowspan=3 align=center><b>Justification</b></td>
                                </tr>
                                <tr>
                                    <?php if($suppliers[$rfq->id]){ ?>
                                        <?php foreach($suppliers[$rfq->id] as $idx => $supplier){ ?>
                                            <?php $totals[$rfq->id][$supplier->id] = 0; ?>
                                            <td align=center><b><?= $letters[$idx] ?>. <?= $supplier->business_name ?></b></td>
                                        <?php } ?>
                                    <?php } ?>
                                </tr>
                                <tr>
                                    <?php if($suppliers[$rfq->id]){ ?>
                                        <?php foreach($suppliers[$rfq->id] as $idx => $supplier){ ?>
                                            <td align=center><?= Html::button('Edit Entries', ['value' => Url::to(['/v1/pr/update-retrieve-rfq', 'id' => $model->id, 'rfq_id' => $rfq->id, 'supplier_id' => $supplier->id]), 'class' => 'btn btn-primary btn-xs btn-block update-retrieve-rfq-button']) ?></td>
                                        <?php } ?>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(!empty($rfqItems)){ ?>
                                <?php $i = 1; ?>
                                <?php foreach($rfqItems as $rfqItem){ ?>
                                    <tr style="background-color: <?= isset($winners[$rfq->id][$rfqItem['id']]) ? !empty($winners[$rfq->id][$rfqItem['id']]['winner']) ? 'transparent' : '#dd4b39' : 'transparent' ?>">
                                        <td align=center><?= $i ?></td>
                                        <td><?= $rfqItem['item'] ?></td>
                                        <td align=center><?= $rfqItem['unit'] ?></td>
                                        <td align=center><?= number_format($rfqItem['total'], 0) ?></td>
                                        <td align=right><?= number_format($rfqItem['cost'], 2) ?></td>
                                        <td align=right><b><?= number_format($rfqItem['total'] * $rfqItem['cost'], 2) ?></b></td>
                                        <?php if($suppliers[$rfq->id]){ ?>
                                            <?php foreach($suppliers[$rfq->id] as $supplier){ ?>
                                                <td align=right style="width: 15%; background-color: <?= isset($winners[$rfq->id][$rfqItem['id']][$supplier->id]) ? $winners[$rfq->id][$rfqItem['id']][$supplier->id]['status'] == 'Awarded' ? 'yellow' : 'transparent' : 'transparent' ?>"><b><?= isset($costs[$rfq->id][$rfqItem['id']][$supplier->id]['cost']) ? $costs[$rfq->id][$rfqItem['id']][$supplier->id]['cost'] > 0 ? number_format($rfqItem['total'] * $costs[$rfq->id][$rfqItem['id']][$supplier->id]['cost'], 2) : '-' : '-' ?></b></td>
                                                <?php $totals[$rfq->id][$supplier->id] += isset($costs[$rfq->id][$rfqItem['id']][$supplier->id]['cost']) ? $rfqItem['total'] * $costs[$rfq->id][$rfqItem['id']][$supplier->id]['cost'] : 0; ?>
                                            <?php } ?>
                                        <?php } ?>
                                        <td><?= isset($winners[$rfq->id][$rfqItem['id']]) ? !empty($winners[$rfq->id][$rfqItem['id']]['winner']) ? $winners[$rfq->id][$rfqItem['id']]['winner']->business_name : 'Failed' : '' ?></td>
                                        <td><?= isset($winners[$rfq->id][$rfqItem['id']]['justification']) ? $winners[$rfq->id][$rfqItem['id']]['justification'] : '' ?></td>
                                        <?php $abcTotal[$rfq->id] += $rfqItem['total'] * $rfqItem['cost'] ?>
                                    </tr>
                                    <?php $i++; ?>
                                <?php } ?>
                            <?php } ?>
                            <tr>
                                <td colspan=5 align=right><b>Total ABC</b></td>
                                <td align=right><b><?= number_format($abcTotal[$rfq->id], 2) ?></b></td>
                                <?php if($suppliers[$rfq->id]){ ?>
                                    <?php foreach($suppliers[$rfq->id] as $supplier){ ?>
                                        <td align=right><b><?= number_format($totals[$rfq->id][$supplier->id], 2) ?></b></td>
                                    <?php } ?>
                                <?php } ?>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>

<?php
  Modal::begin([
    'id' => 'retrieve-rfq-modal',
    'size' => "modal-lg",
    'header' => '<div id="retrieve-rfq-modal-header"><h4>Retrieve RFQ</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="retrieve-rfq-modal-content"></div>';
  Modal::end();
?>

<?php
  Modal::begin([
    'id' => 'update-retrieve-rfq-modal',
    'size' => "modal-lg",
    'header' => '<div id="update-retrieve-rfq-modal-header"><h4>Edit RFQ</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-retrieve-rfq-modal-content"></div>';
  Modal::end();
?>

<?php
  Modal::begin([
    'id' => 'bid-modal',
    'size' => "modal-lg",
    'header' => '<div id="bid-modal-header"><h4>Set Bidding</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="bid-modal-content"></div>';
  Modal::end();
?>

<?php
  Modal::begin([
    'id' => 'update-bid-modal',
    'size' => "modal-lg",
    'header' => '<div id="update-bid-modal-header"><h4>Edit Bidding</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-bid-modal-content"></div>';
  Modal::end();
?>

<?php
  Modal::begin([
    'id' => 'winner-modal',
    'size' => "modal-lg",
    'header' => '<div id="winner-modal-header"><h4>Set Winners</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="winner-modal-content"></div>';
  Modal::end();
?>

<?php
    $script = '
        $(document).ready(function(){
            $("#retrieve-rfq-button").click(function(){
                $("#retrieve-rfq-modal-content").html("");
                $("#retrieve-rfq-modal").modal("show").find("#retrieve-rfq-modal-content").load($(this).attr("value"));
            });

            $(".update-retrieve-rfq-button").click(function(){
                $("#retrieve-rfq-modal-content").html("");
                $("#update-retrieve-rfq-modal").modal("show").find("#update-retrieve-rfq-modal-content").load($(this).attr("value"));
              });
              $(".bid-button").click(function(){
                $("#bid-modal-content").html("");
                $("#bid-modal").modal("show").find("#bid-modal-content").load($(this).attr("value"));
              });
              $(".update-bid-button").click(function(){
                $("#update-bid-modal-content").html("");
                $("#update-bid-modal").modal("show").find("#update-bid-modal-content").load($(this).attr("value"));
              });
              $(".winner-button").click(function(){
                $("#winner-modal-content").html("");
                $("#winner-modal").modal("show").find("#winner-modal-content").load($(this).attr("value"));
              });
        });
        
        function deleteBid(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/delete-bid']).'?id=" + id,
                success: function (data) {
                    console.log(this.data);
                    alert("Bid Details Deleted");
                    retrieveQuotations('.$model->id.');
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
        ';

    $this->registerJs($script, View::POS_END);
?>