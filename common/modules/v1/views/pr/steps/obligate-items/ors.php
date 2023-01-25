<h3 class="panel-title">ORS No. <?= $ors->ors_no ?></h3>
<br>
<table class="table table-bordered table-responsive table-condensed">
    <tr>
        <td colspan=10 align=center><h4><b>OBLIGATION REQUEST AND STATUS</b></h4></td>
        <td><?= is_null($po) ? 'ORS ' : 'Serial ' ?>No.</td>
        <td colspan=2 style="color: <?= $model->fundSource->code == 'NRO' ? '#0070c0' : 'red' ?>"><b><?= $ors->ors_no ?></b></td>
    </tr>
    <tr>
        <td colspan=10 align=center style="color: <?= $model->fundSource->code == 'NRO' ? '#0070c0' : 'red' ?>"><b><?= strtoupper($model->fundSource->description) ?></b></td>
        <td>Date:</td>
        <td colspan=2 style="background-color: #DAEEF3; color: <?= $model->fundSource->code == 'NRO' ? 'black' : 'red' ?>"><b><?= date("F j, Y", strtotime($ors->ors_date)) ?></b></td>
    </tr>
    <tr>
        <td colspan=10 align=center><i>Entity Name</i></td>
        <td>Fund Cluster:</td>
        <td colspan=2 style="color: <?= $model->fundSource->code == 'NRO' ? '#0070c0' : 'red' ?>"><b><?= $model->fundSource->code == 'NRO' ? 'NEDA '.$model->fundCluster->code : $model->fundSource->code.' '.$model->fundCluster->code ?></b></td>
    </tr>
    <tr>
        <td style="width: 7%; border-top: none; border-right: none; border-bottom: none;">&nbsp;</td>
        <td style="width: 5%; border: none;">&nbsp;</td>
        <td style="width: 5%; border: none;">&nbsp;</td>
        <td style="width: 7%; border: none;">&nbsp;</td>
        <td style="width: 5%; border: none;">&nbsp;</td>
        <td style="width: 7%; border: none;">&nbsp;</td>
        <td style="width: 3%; border: none;">&nbsp;</td>
        <td style="width: 21%; border: none;">&nbsp;</td>
        <td style="width: 7%; border: none;">&nbsp;</td>
        <td style="width: 5%; border: none;">&nbsp;</td>
        <td style="width: 9%; border: none;">&nbsp;</td>
        <td style="width: 9%; border: none;">&nbsp;</td>
        <td style="width: 9%; border-top: none; border-left: none; border-bottom: none">&nbsp;</td>
    </tr>
    <tr>
        <td colspan=3 align=center>Payee</td>
        <td colspan=10 style="background-color: #DAEEF3;"><b><?= !is_null($po) ? strtoupper($supplier->business_name.' by '.$po->represented_by) : strtoupper($ors->payee) ?></b></td>
    </tr>
    <tr>
        <td colspan=3 align=center>Office</td>
        <td colspan=10><b><?= !is_null($po) ? '' : $ors->office ?></b></td>
    </tr>
    <tr>
        <td colspan=3 align=center>Address</td>
        <td colspan=10><b><?= !is_null($po) ? $supplier->business_address : $ors->address ?></b></td>
    </tr>
    <tr>
        <td colspan=3 align=center>Responsibility Center</td>
        <td colspan=5 align=center>Particulars</td>
        <td colspan=2 align=center>MFO/PAP</td>
        <td align=center>UACS Object <br> Code</td>
        <td colspan=2 align=center>Amount</td>
    </tr>
    <tr>
        <td colspan=3 rowspan=<?= $rowspan + 4 ?> style="border-bottom: none;" align=center valign=top><?= $ors->responsibility_center ?></td>
        <td colspan=5 style="background-color: #DAEEF3;" rowspan=<?= $rowspan + 4 ?> style="border-bottom: none;" valign=top><?= $model->purpose ?></td>
        <?php if(!empty($prexcData)){ ?>
            <?php foreach($prexcData as $pap => $data){ ?>
                <td colspan=2 rowspan=<?= count($data) ?> align=center style="background-color: #DAEEF3; border-top: none; border-bottom: none;" valign=top><?= $pap ?></td>
                <?php if(!empty($data)){ ?>
                    <?php foreach($data as $obj => $total){ ?>
                            <td align=center style="border-top: none; border-bottom: none;"><?= $obj ?></td>
                            <td colspan=2 align=right style="background-color: #DAEEF3; border-top: none; border-bottom: none;"><b><?= number_format($total, 2) ?></b></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php for($j = 0; $j <= 3; $j++){ ?>
        <tr>
            <td colspan=2 style="border-top: none; border-bottom: none;">&nbsp;</td>
            <td style="border-top: none; border-bottom: none;">&nbsp;</td>
            <td colspan=2 style="border-top: none; border-bottom: none;">&nbsp;</td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan=3 style="border-top: none;">&nbsp;</td>
        <td colspan=5 style="border-top: none;" align=right>Total</td>
        <td colspan=2 style="border-top: none;">&nbsp;</td>
        <td style="border-top: none;">&nbsp;</td>
        <td colspan=2 align=right><h4><b><?= number_format($ors->total, 2) ?></b></h4></td>
    </tr>
    <tr>
        <td><b>A.</b></td>
        <td colspan=2 style="border-bottom: none; border-right: none;"><b>Certified:</b></td>
        <td colspan=5 style="border-bottom: none; border-left: none;">Charges to appropriation/alloment are</td>
        <td><b>B.</b></td>
        <td style="border-bottom: none; border-right: none;"><b>Certified:</b></td>
        <td colspan=3 style="border-bottom: none; border-left: none;">Allotment available and obligated</td>
    </tr>
    <tr>
        <td style="border-right: none; border-bottom: none; border-top: none;">&nbsp;</td>
        <td colspan=7 style="border-left: none; border-bottom: none; border-top: none;">necessary, lawful and under my direct supervision; and</td>
        <td style="border: none;">&nbsp;</td>
        <td colspan=4 style="border-left: none; border-bottom: none; border-top: none;">for the purpose/adjustment necessary as</td>
    </tr>
    <tr>
        <td style="border-right: none; border-bottom: none; border-top: none;">&nbsp;</td>
        <td colspan=7 style="border-left: none; border-bottom: none; border-top: none;">supporting documents valid, proper and legal</td>
        <td style="border: none;">&nbsp;</td>
        <td colspan=4 style="border-left: none; border-bottom: none; border-top: none;">indicated above</td>
    </tr>
    <tr>
        <td style="border-right: none; border-bottom: none; border-top: none;">&nbsp;</td>
        <td colspan=7 style="border-left: none; border-bottom: none; border-top: none;">&nbsp;</td>
        <td style="border: none;">&nbsp;</td>
        <td colspan=4 style="border-left: none; border-bottom: none; border-top: none;">&nbsp;</td>
    </tr>
        <td style="border-right: none; border-bottom: none; border-top: none;"></td>
        <td colspan=7 style="border-left: none; border-bottom: none; border-top: none;">&nbsp;</td>
        <td style="border: none;">&nbsp;</td>
        <td colspan=4 style="border-left: none; border-bottom: none; border-top: none;">&nbsp;</td>
    </tr>
    <tr>
        <td style="border-right: none; border-bottom: none; border-top: none;"></td>
        <td colspan=7 style="border-left: none; border-bottom: none; border-top: none;">&nbsp;</td>
        <td style="border: none;">&nbsp;</td>
        <td colspan=4 style="border-left: none; border-bottom: none; border-top: none;">&nbsp;</td>
    </tr>
    <tr>
        <td colspan=2 style="border-right: none; border-bottom: none; border-top: none;">Signature&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td colspan=6 style="border-left: none; border-top: none;">&nbsp;</td>
        <td colspan=2 style="border: none;">Signature&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td colspan=3 style="border-left: none; border-top: none;">&nbsp;</td>
    </tr>
    <tr>
        <td colspan=2 style="border-right: none; border-bottom: none; border-top: none;">Printed Name&nbsp;&nbsp;&nbsp;:</td>
        <td colspan=6 style="background-color: #DAEEF3; border-left: none; border-top: none;" align=center><h4><?= $model->requesterName ?></h4></td>
        <td colspan=2 style="border: none;">Printed Name&nbsp;&nbsp;&nbsp;:</td>
        <td colspan=3 style="border-left: none; border-top: none;" align=center><h4><?= $ors->reviewerName ?></h4></td>
    </tr>
    <tr>
        <td colspan=2 style="border-right: none; border-bottom: none; border-top: none;">Position&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td colspan=6 style="background-color: #DAEEF3; border-left: none; border-top: none;" align=center><i><?= $model->requester ? $model->requester->position : '' ?></i></td>
        <td colspan=2 style="border: none;">Position&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td colspan=6 style="border-left: none; border-top: none;" align=center><i>Designated Budget Officer</i></td>
    </tr>
    <tr>
        <td colspan=2 style="border-right: none; border-bottom: none; border-top: none;">Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td colspan=6 style="border-left: none; border-top: none;">&nbsp;</td>
        <td colspan=2 style="border: none;">Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td colspan=3 style="border-left: none; border-top: none;">&nbsp;</td>
    </tr>
    <tr>
        <td style="border-right: none; border-bottom: none; border-top: none;"></td>
        <td colspan=7 style="border-left: none; border-bottom: none; border-top: none;">&nbsp;</td>
        <td style="border: none;">&nbsp;</td>
        <td colspan=4 style="border-left: none; border-top: none;">&nbsp;</td>
    </tr>
    <tr>
        <td style="border-right: none; border-bottom: none; border-top: none;"></td>
        <td colspan=7 style="border-left: none; border-bottom: none; border-top: none;">&nbsp;</td>
        <td style="border: none;">&nbsp;</td>
        <td colspan=4 style="border-left: none; border-top: none;">&nbsp;</td>
    </tr>
    <tr>
        <td colspan=13>&nbsp;</td>
    </tr>
    <tr>
        <td><b>C.</b></td>
        <td colspan=12 align=center><b>STATUS OF OBLIGATION</b></td>
    </tr>
    <tr>
        <td colspan=7 align=center><b>Reference</b></td>
        <td colspan=6 align=center><b>Amount</b></td>
    </tr>
    <tr>
        <td align=center>Date</td>
        <td colspan=3 align=center>Particulars</td>
        <td colspan=3 align=center>ORS/JEV/Check/<br>ADA/TRA No.</td>
        <td align=center>Obligation</td>
        <td colspan=2 align=center>Payable</td>
        <td align=center>Payment</td>
        <td colspan=2 align=center>Balance</td>
    </tr>
    <?php if(!empty($items)){ ?>
        <?php $j = 0; ?>
        <?php foreach($items as $item){ ?>
            <?php if($j == 0){ ?>
                <tr>
                    <td align=center style="border-top: none; border-bottom: none;"><?= date("m/d/Y") ?></td>
                    <td colspan=3 style="border-top: none; border-bottom: none;"><?= $item['item'] ?></td>
                    <td colspan=3 align=center style="border-top: none; border-bottom: none;"><?= $ors->ors_no ?></td>
                    <td align=right style="border-top: none; border-bottom: none;"><b><?= number_format($item['total'] * $item['offer'], 2 ) ?></b></td>
                    <td colspan=2 align=center style="border-top: none; border-bottom: none;">&nbsp;</td>
                    <td rowspan=<?= count($items) ?> align=right style="border-top: none; border-bottom: none;"><b><?= number_format($ors->total, 2 ) ?></b></td>
                    <td style="border-top: none; border-bottom: none;">&nbsp;</td>
                    <td style="border-top: none; border-bottom: none;">&nbsp;</td>
                </tr>
            <?php }else{ ?>
                <tr>
                    <td align=center style="border-top: none; border-bottom: none;"><?= date("m/d/Y") ?></td>
                    <td colspan=3 style="border-top: none; border-bottom: none;"><?= $item['item'] ?></td>
                    <td colspan=3 align=center style="border-top: none; border-bottom: none;"><?= $ors->ors_no ?></td>
                    <td align=right style="border-top: none; border-bottom: none;"><b><?= number_format($item['total'] * $item['offer'], 2 ) ?></b></td>
                    <td colspan=2 align=center style="border-top: none; border-bottom: none;">&nbsp;</td>
                    <td style="border-top: none; border-bottom: none;">&nbsp;</td>
                    <td style="border-top: none; border-bottom: none;">&nbsp;</td>
                </tr>
            <?php } ?>
            <?php $j++; ?>
        <?php } ?>
    <?php } ?>
    <tr>
        <td align=center style="border-top: none;">&nbsp;</td>
        <td colspan=3 style="border-top: none;">&nbsp;</td>
        <td colspan=3 align=center style="border-top: none;">&nbsp;</td>
        <td align=right style="border-top: none;">&nbsp;</td>
        <td colspan=2 align=center style="border-top: none;">&nbsp;</td>
        <td align=right style="border-top: none;">&nbsp;</td>
        <td style="border-top: none;">&nbsp;</td>
        <td style="border-top: none;">&nbsp;</td>
    </tr>
</table>