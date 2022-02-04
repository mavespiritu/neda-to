
<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
?>

<tr>
    <td><?= $i ?></td>
    <td><?= $item['unit'] ?></td>
    <td style="width: 20%;"><?= $item['item'] ?></td>
    <td style="width: 15%;"><?= !empty($specifications[$item['id']]) ? \file\components\AttachmentsTable::widget(['model' => $specifications[$item['id']]]) : '' ?></td>
    <td align=center><?= number_format($item['total'], 0) ?></td>
    <td align=right><?= number_format($item['cost'], 2) ?></td>
    <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
    <td><?= $item['supplier'] != '' ? $item['supplier'] : 'No set supplier' ?></td>
    <td align=right><?= number_format($item['abc'], 2) ?></td>
</tr>
<?php
  Modal::begin([
    'id' => 'create-specification-pr-'.$item['id'].'-modal',
    'size' => "modal-md",
    'header' => '<div id="create-specification-pr-'.$item['id'].'-modal-header"><h4>Create Specification</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-specification-pr-'.$item['id'].'-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-specification-pr-'.$item['id'].'-modal',
    'size' => "modal-md",
    'header' => '<div id="update-specification-pr-'.$item['id'].'-modal-header"><h4>Edit Specification</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-specification-pr-'.$item['id'].'-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(document).ready(function(){
            $("#create-specification-pr-'.$item['id'].'-button").click(function(){
              $("#create-specification-pr-'.$item['id'].'-modal").modal("show").find("#create-specification-pr-'.$item['id'].'-modal-content").load($(this).attr("value"));
            });
            $("#update-specification-pr-'.$item['id'].'-button").click(function(){
              $("#update-specification-pr-'.$item['id'].'-modal").modal("show").find("#update-specification-pr-'.$item['id'].'-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>