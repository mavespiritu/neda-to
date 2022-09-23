<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;

$buttonItems = [];
?>
<tr>
  <td>&nbsp;</td>
  <td style="width: 5%">
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-bars"></i>
      </button>
      <ul class="dropdown-menu">
        <?php if($model->status->status == 'Draft' || $model->status->status == 'For Revision'){ ?>
          <li><?= Html::a('Edit Item', ['#'], ['id' => 'update-'.$item['id'].'-button', 'value' => Url::to(['/v1/ris/update-item', 'id' => $model->id, 'activity_id' => $item['activityId'], 'item_id' => $item['stockNo'], 'cost' => $item['cost'], 'type' => 'Original'])]) ?></li>
          <li><?= Html::a('Delete Item', ['delete-item', 'id' => $model->id, 'activity_id' => $item['activityId'], 'item_id' => $item['stockNo'], 'cost' => $item['cost'], 'type' => 'Original'], [
            'data' => [
                'confirm' => 'Are you sure you want to remove this item?',
                'method' => 'post',
            ],
        ]) ?></li>
        <?php } ?>
        <li role="separator" class="divider"></li>
        <!-- If specs not available -->
        <?php if(!isset($specifications[$item['id']])){ ?>
          <?php if($model->status->status == 'Draft' || $model->status->status == 'For Revision'){ ?>
            <li><?= Html::a('Write Specs', ['#'], ['id' => 'create-specification-'.$item['id'].'-button', 'value' => Url::to(['/v1/ris/create-specification', 'id' => $model->id, 'activity_id' => $item['activityId'], 'sub_activity_id' => $item['subActivityId'], 'item_id' => $item['stockNo'], 'cost' => $item['cost'], 'type' => 'Original'])]) ?></li>
            <li><?= Html::a('Attach Specs', ['#'], ['id' => 'attach-specification-'.$item['id'].'-button', 'value' => Url::to(['/v1/ris/attach-specification', 'id' => $model->id, 'activity_id' => $item['activityId'], 'sub_activity_id' => $item['subActivityId'], 'item_id' => $item['stockNo'], 'cost' => $item['cost'], 'type' => 'Original'])]) ?></li>
          <?php } ?>
        <!-- If specs available -->
        <?php }else{ ?>
          <?php if($model->status->status == 'Draft' || $model->status->status == 'For Revision'){ ?>
            <li><?= Html::a('Attach Specs', ['#'], ['id' => 'attach-specification-'.$item['id'].'-button', 'value' => Url::to(['/v1/ris/attach-specification', 'id' => $model->id, 'activity_id' => $item['activityId'], 'sub_activity_id' => $item['subActivityId'], 'item_id' => $item['stockNo'], 'cost' => $item['cost'], 'type' => 'Original'])]) ?></li>
            <?php if($specifications[$item['id']]->risItemSpecValueString != ''){ ?>
              <li><?= Html::a('Edit Specs', ['#'], ['id' => 'update-specification-'.$item['id'].'-button', 'value' => Url::to(['/v1/ris/update-specification', 'id' => $model->id, 'activity_id' => $item['activityId'], 'sub_activity_id' => $item['subActivityId'], 'item_id' => $item['stockNo'], 'cost' => $item['cost'], 'type' => 'Original'])]) ?></li>
            <?php } ?>
            <li><?= Html::a('Delete Specs', ['delete-specification', 'id' => $model->id, 'activity_id' => $item['activityId'], 'sub_activity_id' => $item['subActivityId'], 'item_id' => $item['stockNo'], 'cost' => $item['cost'], 'type' => 'Original'], [
                  'data' => [
                      'confirm' => 'Are you sure you want to remove uploaded/written specification?',
                      'method' => 'post',
                  ],
                ]) ?></li>
          <?php } ?>
        <?php } ?>
      </ul>
    </div>
    </td>
    <td><?= $item['itemTitle'] ?></td>
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
    <td align=right><?= number_format($item['cost'], 2) ?></td>
    <td align=center><?= number_format($item['total'], 0) ?></td>
    <td align=right><?= number_format($item['cost'] * $item['total'], 2) ?></td>
</tr>
<?php
  Modal::begin([
    'id' => 'update-'.$item['id'].'-modal',
    'size' => "modal-md",
    'header' => '<div id="update-'.$item['id'].'-modal-header"><h4>Edit Item</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-'.$item['id'].'-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'attach-specification-'.$item['id'].'-modal',
    'size' => "modal-md",
    'header' => '<div id="attach-specification-'.$item['id'].'-modal-header"><h4>Attach Specification</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="attach-specification-'.$item['id'].'-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'create-specification-'.$item['id'].'-modal',
    'size' => "modal-md",
    'header' => '<div id="create-specification-'.$item['id'].'-modal-header"><h4>Create Specification</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-specification-'.$item['id'].'-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-specification-'.$item['id'].'-modal',
    'size' => "modal-md",
    'header' => '<div id="update-specification-'.$item['id'].'-modal-header"><h4>Edit Specification</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-specification-'.$item['id'].'-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(document).ready(function(){
            $("#update-'.$item['id'].'-button").click(function(e){
              e.preventDefault();
              $("#update-'.$item['id'].'-modal").modal("show").find("#update-'.$item['id'].'-modal-content").load($(this).attr("value"));
            });
            $("#attach-specification-'.$item['id'].'-button").click(function(e){
              e.preventDefault();
              $("#attach-specification-'.$item['id'].'-modal").modal("show").find("#attach-specification-'.$item['id'].'-modal-content").load($(this).attr("value"));
            });
            $("#create-specification-'.$item['id'].'-button").click(function(e){
              e.preventDefault();
              $("#create-specification-'.$item['id'].'-modal").modal("show").find("#create-specification-'.$item['id'].'-modal-content").load($(this).attr("value"));
            });
            $("#update-specification-'.$item['id'].'-button").click(function(e){
              e.preventDefault();
              $("#update-specification-'.$item['id'].'-modal").modal("show").find("#update-specification-'.$item['id'].'-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>