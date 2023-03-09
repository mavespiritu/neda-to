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
<?php $total = 0; ?>
<table style="width: 100%">
    <thead>
        <tr>
            <td align=center><b>#</b></td>
            <td align=center><b>RIS No.</b></td>
            <td align=center><b>PR No/s.</b></td>
            <td align=center><b>Type</b></td>
            <td align=center><b>Division</b></td>
            <td align=center><b>Purpose</b></td>
            <td align=center><b>Fund Source</b></td>
            <td align=center><b>Fund Cluster</b></td>
            <td align=center><b>Created By</b></td>
            <td align=center><b>Date Created</b></td>
            <td align=center><b>Requested By</b></td>
            <td align=center><b>Date Requested</b></td>
            <td align=center><b>Date Required</b></td>
            <td align=center><b>Included PREXCs</b></td>
            <td align=center><b>Realigned PREXCs</b></td>
            <td align=center><b>Total</b></td>
        </tr>
    </thead>
    <tbody>
    <?php if(!empty($items)){ ?>
        <?php foreach($items as $item){ ?>
            <tr>
                <td align=center><?= $i ?></td>
                <td><?= $item['ris_no'] ?></td>
                <td><?= $item['pr_no'] ?></td>
                <td><?= $item['type'] ?></td>
                <td><?= $item['office'] ?></td>
                <td><?= $item['purpose'] ?></td>
                <td><?= $item['fundSource'] ?></td>
                <td><?= $item['fundCluster'] ?></td>
                <td><?= ucwords(strtolower($item['creatorName'])) ?></td>
                <td><?= $item['date_created'] ?></td>
                <td><?= ucwords(strtolower($item['requesterName'])) ?></td>
                <td><?= $item['date_requested'] ?></td>
                <td><?= $item['date_required'] ?></td>
                <td><?= $item['includedPrexc'] ?></td>
                <td><?= $item['realignedPrexc'] ?></td>
                <td align=right><?= number_format($item['total'], 2) ?></td>
            </tr>
            <?php $i++ ?>
        <?php } ?>
    <?php } ?>
    </tbody>
</table>

