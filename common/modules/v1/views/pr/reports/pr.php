<style>
    @media print {
        body {-webkit-print-color-adjust: exact;}
    }
    *{ font-family: "Tahoma"; }
    h4{ text-align: center; } 
    p{ font-size: 10px; font-family: "Tahoma";}
    table{
        font-family: "Tahoma";
        border-collapse: collapse;
        width: 100%;
    }
    thead{
        font-size: 12px;
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

<h4 class="text-center"><b>PURCHASE REQUEST</b></h4>

<table class="table table-bordered table-responsive table-hover table-condensed">
    <thead>
        <tr>
            <td colspan=3><b>Entity Name: </b><u><?= $entityName['value'] ?></u></td>
            <td colspan=3><b>Fund Cluster: </b><u><?= $fundCluster->title ?></u></td>
        </tr>
        <tr>
            <td colspan=2 rowspan=2><b>Division: <?= $model->officeName ?></b></td>
            <td colspan=2><b>PR No.: <?= $model->pr_no?></b></td>
            <td colspan=2 rowspan=2><b>Date: <?= date("F j, Y", strtotime($model->date_prepared)) ?></b></td>
        </tr>
        <tr>
            <td colspan=2><b>Responsibility Center Code: <?= implode(",", $rccs ); ?></b></td>
        </tr>
        <tr>
            <td align=center><b>Stock/Property No.</b></td>
            <td align=center><b>Unit</b></th>
            <td align=center><b>Item Description</b></td>
            <td align=center><b>Quantity</b></td>
            <td align=center><b>Unit Cost</b></td>
            <td align=center><b>Total Cost</b></td>
        </tr>
    </thead>
    <tbody>
    <?php $total = 0; ?>
    <?php if(!empty($items)){ ?>
        <?php foreach($items as $item){ ?>
            <tr>
                <td align=center><?= $item['item_id'] ?></td>
                <td align=center><?= $item['unit'] ?></td>
                <td><?= $item['item'] ?></td>
                <td align=center><?= number_format($item['total'], 0) ?></td>
                <td align=right><?= number_format($item['cost'], 2) ?></td>
                <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
            </tr>
            <?php $total += $item['total'] * $item['cost'] ?>
        <?php } ?>
    <?php } ?>
    <?php if(!empty($specifications)){ ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td align=center><i>(Please see attached specifications for your reference)</i></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    <?php } ?>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align=center><b>xxxxxxxxxxxxxx NOTHING FOLLOWS xxxxxxxxxxxxxx</b></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align=right><b><?= number_format($total, 2) ?></b></td>
    </tr>
    <tr>
        <td colspan=6>Purpose: <?= $model->purpose ?></td>
    </tr>
    <tr>
        <td colspan=6>ABC: PHP <?= number_format($total, 2) ?></td>
    </tr>
    <tr>
        <td colspan=2>&nbsp;</td>
        <td>Requested by:</td>
        <td colspan=3>Approved by:</td>
    </tr>
    <tr>
        <td colspan=2>Signature:</td>
        <td>&nbsp;</td>
        <td colspan=3>&nbsp;</td>
    </tr>
    <tr>
        <td colspan=2>Printed Name:</td>
        <td><br><b><?= ucwords(strtoupper($model->requesterName)) ?></b></td>
        <td colspan=3><br><b><?= ucwords(strtoupper($model->approverName)) ?></b></td>
    </tr>
    <tr>
        <td colspan=2>Designation:</td>
        <td><?= $model->requester->position ?></td>
        <td colspan=3><?= $model->approver->position ?></td>
    </tr>
    </tbody>
</table>