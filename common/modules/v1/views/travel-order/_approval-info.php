<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\v1\models\TravelOrder */
?>
<h4>Approval Information
    <?= $this->render('_menu', [
        'model' => $model
    ]) ?>
    <br>
    <span style="font-weight: normal; font-size: 14px;"><?= $model->statusInfo ?></span>
</h4>
<hr style="opacity: 0.1;">
<table class="table table-responsive table-hover">
    <thead>
        <tr>
            <th style="width: 20%;">Status</th>
            <th style="width: 20%;">Acted By</th>
            <th style="width: 20%;">Designation</th>
            <th style="width: 20%;">Date Acted</th>
            <th style="width: 20%;">Remarks</th>
        </tr>
    </thead>
    <tbody>
    <?php if($model->digitalSignatures){ ?>
        <?php foreach($model->getDigitalSignatures()->orderBy(['concat(date_disapproved,"",date_approved)' => SORT_DESC])->all() as $signature){ ?>
        <tr>
            <?php if(!is_null($signature->date_approved) || !is_null($signature->date_disapproved)){ ?>
                <td><?= !is_null($signature->date_approved) ? 'Approved' : 'Disapproved' ?></td>  
            <?php }else{ ?>
                <td>-</td>
            <?php } ?> 
            <td><?= $signature->employee ? $signature->employee->mname != '' ? $signature->employee->fname.' '.substr($signature->employee->mname, 0, 1).'. '.$signature->employee->lname : $signature->employee->fname.' '.$signature->employee->lname : '' ?></td>   
            <td><?= $signature->designation ?></td>
            <?php if(!is_null($signature->date_approved) || !is_null($signature->date_disapproved)){ ?>
                <td><?= !is_null($signature->date_approved) ? date("F j, Y H:i:s", strtotime($signature->date_approved)) : date("F j, Y H:i:s", strtotime($signature->date_disapproved)) ?></td>  
            <?php }else{ ?>
                <td>&nbsp;</td>
            <?php } ?>
            <td><?= $signature->remarks ?></td>    
        </tr>
        <?php } ?>
    <?php }else{ ?>
        <tr>
            <td colspan=5 align=center>No approval info found.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<br>
<br>
<span class="pull-right">
    <?= Html::a('Close', ['/v1/travel-order'], ['class' => 'btn btn-default'])?>
</span>