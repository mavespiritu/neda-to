<h4>ORS No. <?= $ors->ors_no ?></h4>
<br>
<table class="table table-bordered table-responsive">
    <tr>
        <td colspan=10 align=center><b>OBLIGATION REQUEST AND STATUS</b></td>
        <td><?= is_null($po) ? 'ORS ' : 'Serial ' ?>No.</td>
        <td colspan=2><?= $ors->ors_no ?></td>
    </tr>
    <tr>
        <td colspan=10 align=center><?= strtoupper($model->fundSource->description) ?></td>
        <td>Date:</td>
        <td colspan=2><?= date("F j, Y", strtotime($ors->ors_date)) ?></td>
    </tr>
    <tr>
        <td colspan=10 align=center><i>Entity Name</i></td>
        <td>Fund Cluster:</td>
        <td colspan=2><?= $model->fundSource->code == 'NRO' ? 'NEDA '.$model->fundCluster->code : $model->fundSource->code.' '.$model->fundCluster->code ?></td>
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
        <td colspan=10><b><?= !is_null($po) ? strtoupper($supplier->business_name.' by '.$po->represented_by) : strtoupper($ors->payee) ?></b></td>
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
    <?php if(!empty($prexcData)){ ?>
        <?php $x = 0 ?>
        <?php foreach($prexcData as $pap => $data){ ?>
            <?php if($x == 0){ ?>
                <tr>
                <td colspan=3 rowspan=8 align=center><?= $ors->responsibility_center ?></td>
                <td colspan=5 rowspan=7><?= $model->purpose ?></td>
                <td colspan=2 align=center><?= $pap ?></td>
                <?php if(!empty($data)){ ?>
                    <?php foreach($data as $objCode => $total){ ?>
                        <td align=center><?= $objCode ?></td>
                        <td align=right><b><?= number_format($total, 2) ?></b></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            <?php }else{ ?>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            <?php } ?>
        <?php $x++; ?>
        <?php } ?>
    <?php } ?>
    <tr>
        <td colspan=3 rowspan=8 align=center><?= $ors->responsibility_center ?></td>
        <td colspan=5 rowspan=7><?= $model->purpose ?></td>
    </tr>
</table>