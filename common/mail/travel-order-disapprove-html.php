<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
/* @var $this yii\web\View */
/* @var $user common\models\User */

?>
<div class="verify-email">
    <p>Hello <?= Html::encode(Yii::$app->user->identity->userinfo->fullName) ?>,</p>

    <p>Please be informed that your travel order request has been disapproved by the management.</p>

    <p>Below are the details of the travel order request:</p>

    <center>
    <table style="width: 80%;" border="1" cellspacing=0>
        <tr>
            <td style="width: 30%"><b>Travel Order No.</b></td>
            <td style="width: 70%"><?= $model->TO_NO ?></td>
        </tr>
        <tr>
            <td><b>Purpose</b></td>
            <td><?= $model->TO_subject ?></td>
        </tr>
        <tr>
            <td><b>Inclusive Date/s</b></td>
            <td><?= $model->date_from === $model->date_to ? date("F j, Y", strtotime($model->date_from)) : date("F j, Y", strtotime($model->date_from)).' - '.date("F j, Y", strtotime($model->date_to)) ?></td>
        </tr>
        <tr>
            <td><b>Travel Type</b></td>
            <td><?= $model->travelTypeName ?></td>
        </tr>
        <tr>
            <td><b>Request with vehicle?</b></td>
            <td><?= $model->withVehicle === 1 ? 'Yes' : 'No' ?></td>
        </tr>
        <tr>
            <td><b>Created By</b></td>
            <td><?= $model->creatorName ?></td>
        </tr>
        <tr>
            <td><b>Date Created</b></td>
            <td><?= date("F j, Y H:i:s", strtotime($model->date_filed)) ?></td>
        </tr>
        <tr>
            <td><b>Other Passenger/s</b></td>
            <td><?= $model->otherpassenger ?></td>
        </tr>
        <tr>
            <td><b>Other Vehicle/s</b></td>
            <td><?= $model->othervehicle ?></td>
        </tr>
        <tr>
            <td><b>Other Driver/s</b></td>
            <td><?= $model->otherdriver ?></td>
        </tr>
    </table>
    </center>

    <p>To view more, go to <a href="https://nro1-to.neda.gov.ph" target="_blank">NRO1 Travel Order System</a> and login your credentials. Go to travel order list and search for <b>Travel Order No. <?= $model->TO_NO ?></b></p>
</div>
