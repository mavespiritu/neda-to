
<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
?>

<tr>
    <td><?= $i ?></td>
    <td><?= $item['ris_no'] ?></td>
    <td><?= $item['unit'] ?></td>
    <td><?= $item['item'] ?></td>
    <td>
    <?php if(isset($specifications[$item['id']])){ ?>
        <?php if(!empty($specifications[$item['id']]->risItemSpecFiles)){ ?>
        <table style="width: 100%">
        <?php foreach($specifications[$item['id']]->risItemSpecFiles as $file){ ?>
            <tr>
            <td><?= Html::a($file->name.'.'.$file->type, ['/file/file/download', 'id' => $file->id]) ?></td>
            <!-- <td align=right><?= Html::a('<i class="fa fa-trash"></i>', ['/file/file/delete', 'id' => $file->id], [
                    'data' => [
                        'confirm' => 'Are you sure you want to remove this item?',
                        'method' => 'post',
                    ],
                ]) ?></td> -->
            </tr>
        <?php } ?>
        </table>
        <br>
        <?php } ?>
        <?= $specifications[$item['id']]->risItemSpecValueString ?>
    <?php } ?>
    </td>
    <td align=center><?= number_format($item['total'], 0) ?></td>
    <td align=right><?= number_format($item['cost'], 2) ?></td>
    <td align=right><?= number_format($item['total'] * $item['cost'], 2) ?></td>
    <td align=center>
        <?= $form->field($prItems[$item['id']], "[$id]id")->checkbox(['value' => $item['id'], 'class' => 'check-pr-item', 'label' => '', 'id' => 'check-pr-item-'.$item['id'], 'checked' => 'checked']) ?>
    </td>
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