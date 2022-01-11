<?php $i = 1; ?>
<h6 class="text-center"><b>REQUEST AND ISSUANCE SLIP</b></h6>
<p><b>Entity Name: <u><?= $entityName ?></u></b><br>
<b>Fund Cluster: <u><?= $fundClusterName ?></u></b></p>
<?php $total = 0; ?>
<table style="width: 100%">
    <tr>
        <td colspan=2>Division:<br>Office: </td>
        <td colspan=4><u><?= $model->officeName ?></u><br><u><?= $model->officeName ?></u></td>
        <td colspan=5>RIS No. <u><?= $model->ris_no ?></u></td>
    </tr>
    <tr>
        <td colspan=6 align=center><b>Requisition</b></td>
        <td rowspan=2><b>Stock Available?</b></td>
        <td colspan=4 align=center><b>Issue</b></td>
    </tr>
    <tr>
        <td align=center>#</td>
        <td align=center>Stock No.</td>
        <td align=center>Unit</td>
        <td align=center>Description</td>
        <td align=center>Quantity</td>
        <td align=center>ABC</td>
        <td align=center>Quantity</td>
        <td align=center>Date Issue</td>
        <td align=center>Remarks</td>
        <td align=center>Fund Source</td>
    </tr>
    <?php if(!empty($risItems)){ ?>
        <?php foreach($risItems as $idx => $items){ ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><b><?= $idx ?> - <?= $model->fundSource->code ?> Funded</b></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
            <?php if(!empty($items)){ ?>
                <?php foreach($items as $item){ ?>
                    <?= $this->render('_ris-item', [
                        'i' => $i,
                        'model' => $model,
                        'item' => $item
                    ]) ?>
                    <?php $total += ($item['total'] * $item['cost']); ?>
                    <?php $i++; ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    <tr>
        <td colspan=5 align=right><b>Total</b></td>
        <td align=right><b><?= number_format($total, 2) ?></b></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
</table>
<br>
<table style="width: 50%; margin-left: 50%">
    <tr>
        <td style="width: 50%; border: none;">Date Required:</td>
        <td style="border-bottom: 1px solid black; border-top: none; border-right: none; border-left: none;"><?= $model->date_required ?></td>
    </tr>
</table>
<table style="width: 80%;">
    <tr>
        <td style="width: 10%; border: none;">Purpose:</td>
        <td style="width: 80%; border-bottom: 1px solid black; border-top: none; border-right: none; border-left: none;"><?= $model->purpose ?></td>
    </tr>
    <tr>
        <td style="width: 10%; border: none">&nbsp;</td>
        <td style="width: 80%; border: none;"><?= $comment ?></td>
    </tr>
</table>
<br>
<table style="width: 100%;">
    <tr>
        <td>Signature:</td>
        <td>Requested By:</td>
        <td>Approved By:</td>
        <td>Issued By:</td>
        <td>Received By:</td>
    </tr>
    <tr>
        <td>Printed Name:</td>
        <td><br><?= $model->requesterName ?></td>
        <td><br><?= $model->approverName ?></td>
        <td><br><?= $model->issuerName ?></td>
        <td><br><?= $model->receiverName ?></td>
    </tr>
    <tr>
        <td>Designation:</td>
        <td><?= $model->requester ? $model->requester->position : '' ?></td>
        <td><?= $model->approver ? $model->approver->position : '' ?></td>
        <td><?= $model->issuer ? $model->issuer->POSITION_C : '' ?></td>
        <td><?= $model->receiver ? $model->receiver->POSITION_C : '' ?></td>
    </tr>
    <tr>
        <td>Date:</td>
        <td><?= $model->requester ? $model->date_requested : '' ?></td>
        <td><?= $model->approver ? $model->date_approved : '' ?></td>
        <td><?= $model->issuer ? $model->date_issued : '' ?></td>
        <td><?= $model->receiver ? $model->date_received : '' ?></td>
    </tr>
</table>
<br>
<?php if($model->getItemsTotal('Realigned') > 0){ ?>
    <h6 class="text-center"><b>SOURCE OF REALIGNMENT</b></h6>
    <table style="width: 100%;">
        <thead>
            <tr>
                <th>Description</th>
                <?php if($months){ ?>
                    <?php foreach($months as $month){ ?>
                        <th><?= $month->abbreviation ?></th>
                    <?php } ?>
                <?php } ?>
                <th>Justification</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($realignedItems)){ ?>
            <?php foreach($realignedItems as $activity => $raItems){ ?>
                <tr>
                    <td colspan=14><b><?= $activity ?></b></td>
                </tr>
                <?php if(!empty($raItems)){ ?>
                    <?php foreach($raItems as $itemTitle => $ritems){ ?>
                        <tr>
                            <td><?= $itemTitle ?></td>
                            <?php if($months){ ?>
                                <?php foreach($months as $month){ ?>
                                    <td><?= isset($ritems[$month->id]) ? number_format($ritems[$month->id], 0) : 0 ?></td>
                                <?php } ?>
                            <?php } ?>
                            <td><?= $model->purpose ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>
