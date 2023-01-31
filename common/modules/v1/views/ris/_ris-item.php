<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
?>
<tr>
    <td><?= $i ?></td>
    <td align=center><?= $item['stockNo'] ?></td>
    <td><?= $item['unitOfMeasure'] ?></td>
    <td>
        <?php if (!Yii::$app->controller->action->id == 'print') { ?>
            <a href="javascript:void(0)" onClick="viewItemDetails(<?= $item['ppmp_item_id'] ?>)"><?= $item['itemTitle'] ?></a><br>
        <i><?= isset($specifications[$item['id']]) ? $specifications[$item['id']]->risItemSpecValueString : '' ?></i>
        <?php }else{ ?>
            <?= $item['itemTitle'] ?></a><br>
            <i><?= isset($specifications[$item['id']]) ? $specifications[$item['id']]->risItemSpecValueString : '' ?>
        <?php } ?>
    </td>
    <td align=center><?= number_format($item['total'], 0) ?></td>
    <td align=right><?= number_format($item['cost'], 2) ?></td>
    <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>