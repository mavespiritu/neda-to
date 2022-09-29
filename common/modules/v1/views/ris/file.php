<?php if($action != 'pdf'){ ?>
<style>
    *{ font-family: "Tahoma"; }
    h6{ text-align: center; } 
    p{ font-size: 10px; font-family: "Tahoma";}
    table{
        font-family: "Tahoma";
        border-collapse: collapse;
    }
    thead{
        font-size: 12px;
        text-align: center;
    }

    td{
        font-size: 10px;
        border: 1px solid black;
        padding: 3px 3px;
    }

    th{
        font-size: 10px;
        text-align: center;
        border: 1px solid black;
        padding: 3px 3px;
    }
</style>
<?php } ?>
<?php 
use yii\web\View;

$i = 1; 

?>
<h6 class="text-center"><b>REQUEST AND ISSUANCE SLIP</b></h6>
<p><b>Entity Name: <u><?= $entityName ?></u></b><br>
<b>Fund Cluster: <u><?= $fundClusterName ?></u></b></p>
<?php $total = 0; ?>
<table style="width: 100%">
    <tr>
        <td colspan=2>Division:<br>Office: </td>
        <td colspan=5><u><?= $model->officeName ?></u><br><u><?= $model->officeName ?></u></td>
        <td colspan=5>RIS No. <u><?= $model->ris_no ?></u></td>
    </tr>
    <tr>
        <td colspan=7 align=center><b>Requisition</b></td>
        <td rowspan=2><b>Stock Available?</b></td>
        <td colspan=4 align=center><b>Issue</b></td>
    </tr>
    <tr>
        <td align=center>#</td>
        <td align=center>Stock No.</td>
        <td align=center>Unit</td>
        <td align=center>Description</td>
        <td align=center>Quantity</td>
        <td align=center>Unit Cost</td>
        <td align=center>ABC</td>
        <td align=center>Quantity</td>
        <td align=center>Date Issue</td>
        <td align=center>Remarks</td>
        <td align=center>Fund Source</td>
    </tr>
    <?php if(!empty($risItems)){ ?>
        <?php foreach($risItems as $activity => $subActivityitems){ ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan=2><b><?= $activity ?></b></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
            <?php if(!empty($subActivityitems)){ ?>
                <?php foreach($subActivityitems as $subActivity => $items){ ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><b><?= $subActivity ?> - <?= $model->fundSource->code ?> Funded</b></td>
                    <td>&nbsp;</td>
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
                                'item' => $item,
                                'specifications' => $specifications
                            ]) ?>
                            <?php $total += ($item['total'] * $item['cost']); ?>
                            <?php $i++; ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    <tr>
        <td colspan=6 align=right><b>Total</b></td>
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
        <td style="width: 80%; border: none;"><br>
        <?php if($action == 'print'){ ?>
            <?php if($comment == 1){ 
                echo '<input type="checkbox" checked >
                &nbsp;&nbsp;&nbsp;
                All items indicated herein are in the APP
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" >
                &nbsp;&nbsp;&nbsp;
                Some items indicated herein are NOT in the APP';
            }else{
                echo '<input type="checkbox"  >
                &nbsp;&nbsp;&nbsp;
                All items indicated herein are in the APP
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" checked="checked" >
                &nbsp;&nbsp;&nbsp;
                Some items indicated herein are NOT in the APP';
            } 
            ?>
        <?php }else{ ?>
            <?php if($comment == 1){ 
                echo '<i class="fa ">&#f14a;</i>
                &nbsp;&nbsp;&nbsp;
                All items indicated herein are in the APP
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <i class="fa ">&#f096</i>
                &nbsp;&nbsp;&nbsp;
                Some items indicated herein are NOT in the APP';
            }else{
                echo '<i class="fa ">&#f096</i>
                &nbsp;&nbsp;&nbsp;
                All items indicated herein are in the APP
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <i class="fa ">&#f14a;</i>
                &nbsp;&nbsp;&nbsp;
                Some items indicated herein are NOT in the APP';
            } 
            ?>
        <?php } ?>
        </td>
    </tr>
</table>
<br>
<table style="width: 100%;">
    <tr>
        <td style="width: 20%">&nbsp;</td>
        <td style="width: 20%">Requested By:</td>
        <td style="width: 20%">Approved By:</td>
        <td style="width: 20%">Issued By:</td>
        <td style="width: 20%">Received By:</td>
    </tr>
    <tr>
        <td>Signature:</td>
        <td><br><br></td>
        <td><br><br></td>
        <td><br><br></td>
        <td><br><br></td>
    </tr>
    <tr>
        <td>Printed Name:</td>
        <td><?= ucwords(strtoupper($model->requesterName)) ?></td>
        <td><?= ucwords(strtoupper($model->approverName)) ?></td>
        <td><?= ucwords(strtoupper($model->issuerName)) ?></td>
        <td><?= ucwords(strtoupper($model->receiverName)) ?></td>
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
                <th colspan=2>Description</th>
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
            <?php foreach($realignedItems as $activity => $subActivityItems){ ?>
                <tr>
                    <td colspan=15><b><?= $activity ?></b></td>
                </tr>
                <?php if(!empty($subActivityItems)){ ?>
                    <?php foreach($subActivityItems as $subActivity => $raItems){ ?>
                        <tr>
                            <th>&nbsp;</th>
                            <td colspan=14><b><?= $subActivity ?></b></td>
                        </tr>
                        <?php if(!empty($raItems)){ ?>
                            <?php foreach($raItems as $itemTitle => $ritems){ ?>
                                <tr>
                                    <td>&nbsp;</td>
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
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>
