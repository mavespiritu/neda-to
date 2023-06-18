<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\TravelOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Signatories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="signatory-index">
    <br>
    <table class="table table-responsive table-hover">
        <thead>
            <tr>
                <th>Division</th>
                <th>Name of Staff</th>
                <th>Recommending Approval</th>
                <th>Final Approval</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($data)){ ?>
            <?php foreach($data as $division => $staffs){ ?>
                    <tr style="background-color: #F9F9F9;">
                        <td colspan=4><b><?= $division ?></b></td>
                    </tr>
                    <?php if(!empty($staffs)){ ?>
                        <?php foreach($staffs as $staff){ ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td><?= $staff['staff'] ?></td>
                                <td><?= $staff['recommender'] ?></td>
                                <td><?= $staff['approver'] ?></td>
                                <td align=right>
                                    <?= Yii::$app->user->can('Administrator') ? Html::button('<i class="fa fa-edit"></i>', ['value' => Url::to(['/v1/signatory/assign', 'id' => $staff['emp_id']]), 'class' => 'btn btn-sm btn-primary assign-button', 'title' => 'Edit Signatories']) : '' ?>
                                </td>   
                            </tr>
                        <?php } ?>
                    <?php } ?>
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php
  Modal::begin([
    'id' => 'assign-modal',
    'size' => "modal-sm",
    'header' => '<div id="assign-modal-header"><h4>Assign Signatory</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="assign-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(".assign-button").click(function(){
            $("#assign-modal").modal("show").find("#assign-modal-content").load($(this).attr("value"));
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>