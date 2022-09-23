<?php
    use yii\helpers\Html;
    use yii\widgets\DetailView;
    use yii\bootstrap\ButtonDropdown;
    use yii\helpers\Url;

    $total = 0;
?>

<table class="table table-bordered table-condensed table-striped table-hover">
    <thead>
        <tr>
            <th style="width: 5%">&nbsp;</th>
            <th>&nbsp;</th>
            <th style="width: 25%">Item</th>
            <th style="width: 15%">Specification</th>
            <td align=right style="width: 15%"><b>Unit Cost</b></td>
            <td align=center style="width: 15%"><b>Quantity</b></td>
            <td align=right style="width: 15%"><b>Total</b></td>
        </tr>
    </thead>
    <tbody>
    <?php if(!empty($originalItems)){ ?>
        <?php foreach($originalItems as $activity => $activityItems){ ?>
            <tr>
                <th colspan=7><?= $activity ?></th>
            </tr>
            <?php if(!empty($activityItems)){ ?>
                <?php foreach($activityItems as $subActivity => $subActivityItems){ ?>
                    <tr>
                        <th>&nbsp;</th>
                        <th colspan=6><?= $subActivity ?></th>
                    </tr>
                    <?php if(!empty($subActivityItems)){ ?>
                        <?php foreach($subActivityItems as $item){ ?>
                            <?= $this->render('_original-item', [
                                'model' => $model,
                                'item' => $item,
                                'specifications' => $specifications,
                            ]) ?>
                            <?php $total += ($item['cost'] * $item['total']); ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php }else{ ?>
        <tr>
            <td colspan=6 align=center>No original items included</td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan=5 align=right><b>Grand Total</b></td>
        <td align=right><b><?= number_format($total, 2) ?></b></td>
    </tr>
    </tbody>
</table>