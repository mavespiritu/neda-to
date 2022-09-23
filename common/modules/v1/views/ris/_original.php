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
        <?php foreach($originalItems as $activity => $origItems){ ?>
            <tr>
                <th colspan=6><?= $activity ?></th>
            </tr>
            <?php if(!empty($origItems)){ ?>
                <?php foreach($origItems as $item){ ?>
                    <?= $this->render('_original-item', [
                        'model' => $model,
                        'item' => $item,
                        'specifications' => $specifications,
                    ]) ?>
                    <?php $total += ($item['cost'] * $item['total']); ?>
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