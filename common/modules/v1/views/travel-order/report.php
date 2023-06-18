<?php
use frontend\assets\AppAsset;
use yii\helpers\Html;

$asset = AppAsset::register($this);
?>
<link rel="stylesheet" href="<?= $asset->baseUrl.'/css/site.css' ?>" />
<style>
    @media print {
        body {-webkit-print-color-adjust: exact;}
    }
    *{ font-family: "Tahoma"; font-size: 14px;}
    h3, h4{ text-align: center; } 
    p{ font-family: "Tahoma";}
    table{
        font-family: "Tahoma";
        border-collapse: collapse;
        width: 100%;
    }
    table.table-bordered{
        font-family: "Tahoma";
        border-collapse: collapse;
        width: 100%;
    }
    thead{
        font-size: 14px;
    }

    table.table-bordered td{
        font-size: 14px;
        border: 1px solid #555555 !important;
        padding: 3px 3px;
    }

    table.table-bordered th{
        font-size: 14px;
        text-align: center;
        border: 1px solid #555555 !important;
        padding: 3px 3px;
    }
</style>

<div class="content">
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <p class="text-center">
                Republic of the Philippines
                <br>
                <b>NATIONAL ECONOMIC AND DEVELOPMENT AUTHORITY</b>
                <br>
                Regional Office 1
                <br>
                Guerrero Road, City of San Fernando, La Union
                <br>
                <span style="
                    display: inline-block;
                    margin-top: 1.5rem; 
                    margin-bottom: 1.5rem; 
                    padding-top: 0.5rem; 
                    padding-bottom: 0.5rem; 
                    padding-left: 1rem; 
                    padding-right: 1rem; 
                    border: 1px solid black;
                "><b>TRAVEL ORDER NO. <?= $model->TO_NO ?></b></span>
            </p>
            <br>
            <br>
            <span class="pull-right">DATE: <span style="display: inline-block; border-bottom: 1px solid black; width: 200px; text-align: center;"><?= date("F j, Y", strtotime($model->date_filed))?></span></span>
            <span class="clearfix"></span>
            <br>
            <table style="width: 100%; line-height: 30px; border-collapse: collapse;">
                <tr>
                    <td style="width: 15%; vertical-align:top;">TO:</td>
                    <td><span style="display: inline-block; border-bottom: 1px solid black; width: 100%; text-indent: 10px;"><b>CONCERNED STAFF</b></span></td>
                </tr>
                <tr>
                    <td style="width: 15%; vertical-align:top;">PURPOSE:</td>
                    <td><span style="display: inline-block; border-bottom: 1px solid black; width: 100%; text-indent: 10px;"><b><?= $model->TO_subject ?></b></span></td>
                </tr>
                <tr>
                    <td style="width: 15%; vertical-align:top;">DESTINATION:</td>
                    <td>
                        <span style="display: inline-block; border-bottom: 1px solid black; width: 100%; text-indent: 10px;">
                            <?php if($model->travelOrderLocations){ ?>
                                <?php foreach($model->travelOrderLocations as $idx => $location){ ?>
                                    <b><?= $location->location->description.', '.$location->location->citymun->description.', '.$location->location->citymun->province->description ?> <?= $idx !== count($model->travelOrderLocations) - 1 ? '/' : '' ?></b>
                                <?php } ?>
                            <?php } ?>
                        </span>
                    </td>
                </tr>
            </table>
            <br>
            <br>
            <p>1. The following personnel of this Authority are hereby authorized to proceed to official destination <?= $model->date_from !== $model->date_to ? 'from' : 'on' ?> <u><b><?= $model->date_from === $model->date_to ? date("F j, Y", strtotime($model->date_from)) : date("F j, Y", strtotime($model->date_from)).' to '.date("F j, Y", strtotime($model->date_to)) ?></b></u>.</p>
            <br>
            <br>
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <p><b>PERSONNEL</b></p>
                    <?php if(count($staffs) <= 8){ ?>
                        <?php if(!empty($staffs)){ ?>
                            <?php foreach($staffs as $staff){ ?>
                                <span style="display: inline-block; border-bottom: 1px solid black; width: 90%; text-indent: 20px;"><?= $staff ?></span><br>
                            <?php } ?>
                        <?php } ?>
                        <?php if($staffEmptyLines > 0){ ?>
                            <?php for($i = 0; $i < $staffEmptyLines; $i++){ ?>
                                <span style="display: inline-block; border-bottom: 1px solid black; width: 90%;">&nbsp;</span><br>
                            <?php } ?>
                        <?php } ?>
                    <?php }else{ ?>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <?php if(!empty($firstStaffs)){ ?>
                                    <?php foreach($firstStaffs as $staff){ ?>
                                        <span style="display: inline-block; border-bottom: 1px solid black; width: 100%;"><?= $staff ?></span><br>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <?php if(!empty($secondStaffs)){ ?>
                                    <?php foreach($secondStaffs as $staff){ ?>
                                        <span style="display: inline-block; border-bottom: 1px solid black; width: 100%;"><?= $staff ?></span><br>
                                    <?php } ?>
                                <?php } ?>
                                <?php if($doubleStaffEmptyLines > 0){ ?>
                                    <?php for($i = 0; $i < $doubleStaffEmptyLines; $i++){ ?>
                                        <span style="display: inline-block; border-bottom: 1px solid black; width: 100%;">&nbsp;</span><br>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6 col-xs-12">
                    <p><b>RECOMMENDING APPROVAL</b></p>
                    <?php if(!empty($recommenders)){ ?>
                        <?php foreach($recommenders as $recommender){ ?>
                            <span style="display: inline-block; border-bottom: 1px solid black; width: 80%; text-indent: 20px;"><?= $recommender ?></span><br>
                        <?php } ?>
                    <?php } ?>
                    <?php if($recommenderEmptyLines > 0){ ?>
                        <?php for($i = 0; $i < $recommenderEmptyLines; $i++){ ?>
                            <span style="display: inline-block; border-bottom: 1px solid black; width: 80%;">&nbsp;</span><br>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <br>
            <p>2. Per approved Itinerary of Travel, expenses are hereby authorized, subject to availability of funds  and the usual accounting and auditing rules and regulations, chargeable against the fund of the:</p>
            <br>
            <center><span style="display: inline-block; border-bottom: 1px solid black; width: 40%; text-align: center;"><b>NEDA REGIONAL OFFICE 1</b></span></center>
            <br>
            <p>3. Upon completion of the travel, the Certificate of Appearance, Certificate of Travel Completed and a Report on the purpose shall be submitted to the office.</p>
            <br>
            <p style="margin-left: 5rem;"><b>APPROVED:</b></p>
            <br>
            <br>
            <br>
            <center>
                <span style="display: inline-block; border-bottom: 1px solid black; width: 40%; text-align: center;"><b><?= isset($approver[0]) ? $approver[0] : '' ?></b></span>
                <br>
                <?= isset($approver[0]) ? $approver[0] == 'Irenea Ubungen' ? 'OIC-Regional Director' : 'OIC-Assistant Regional Director' : '' ?>
            </center>
        </div>
        <div class="col-md-6 col-xs-12">
            <p class="text-center">
                Republic of the Philippines
                <br>
                <b>NATIONAL ECONOMIC AND DEVELOPMENT AUTHORITY</b>
                <br>
                Regional Office 1
                <br>
                Guerrero Road, City of San Fernando, La Union
                <br>
                <span style="
                    display: inline-block;
                    margin-top: 1.5rem; 
                    margin-bottom: 1.5rem; 
                    padding-top: 0.5rem; 
                    padding-bottom: 0.5rem; 
                    padding-left: 1rem; 
                    padding-right: 1rem; 
                    border: 1px solid black;
                "><b>TRAVEL ORDER NO. <?= $model->TO_NO ?></b></span>
            </p>
            <br>
            <br>
            <span class="pull-right">DATE: <span style="display: inline-block; border-bottom: 1px solid black; width: 200px; text-align: center;"><?= date("F j, Y", strtotime($model->date_filed))?></span></span>
            <span class="clearfix"></span>
            <br>
            <table style="width: 100%; line-height: 30px; border-collapse: collapse;">
                <tr>
                    <td style="width: 15%; vertical-align:top;">TO:</td>
                    <td><span style="display: inline-block; border-bottom: 1px solid black; width: 100%; text-indent: 10px;"><b>CONCERNED STAFF</b></span></td>
                </tr>
                <tr>
                    <td style="width: 15%; vertical-align:top;">PURPOSE:</td>
                    <td><span style="display: inline-block; border-bottom: 1px solid black; width: 100%; text-indent: 10px;"><b><?= $model->TO_subject ?></b></span></td>
                </tr>
                <tr>
                    <td style="width: 15%; vertical-align:top;">DESTINATION:</td>
                    <td>
                        <span style="display: inline-block; border-bottom: 1px solid black; width: 100%; text-indent: 10px;">
                            <?php if($model->travelOrderLocations){ ?>
                                <?php foreach($model->travelOrderLocations as $idx => $location){ ?>
                                    <b><?= $location->location->description.', '.$location->location->citymun->description.', '.$location->location->citymun->province->description ?> <?= $idx !== count($model->travelOrderLocations) - 1 ? '/' : '' ?></b>
                                <?php } ?>
                            <?php } ?>
                        </span>
                    </td>
                </tr>
            </table>
            <br>
            <br>
            <p>1. The following personnel of this Authority are hereby authorized to proceed to official destination <?= $model->date_from !== $model->date_to ? 'from' : 'on' ?> <u><b><?= $model->date_from === $model->date_to ? date("F j, Y", strtotime($model->date_from)) : date("F j, Y", strtotime($model->date_from)).' to '.date("F j, Y", strtotime($model->date_to)) ?></b></u>.</p>
            <br>
            <br>
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <p><b>PERSONNEL</b></p>
                    <?php if(count($staffs) <= 8){ ?>
                        <?php if(!empty($staffs)){ ?>
                            <?php foreach($staffs as $staff){ ?>
                                <span style="display: inline-block; border-bottom: 1px solid black; width: 90%; text-indent: 20px;"><?= $staff ?></span><br>
                            <?php } ?>
                        <?php } ?>
                        <?php if($staffEmptyLines > 0){ ?>
                            <?php for($i = 0; $i < $staffEmptyLines; $i++){ ?>
                                <span style="display: inline-block; border-bottom: 1px solid black; width: 90%;">&nbsp;</span><br>
                            <?php } ?>
                        <?php } ?>
                    <?php }else{ ?>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <?php if(!empty($firstStaffs)){ ?>
                                    <?php foreach($firstStaffs as $staff){ ?>
                                        <span style="display: inline-block; border-bottom: 1px solid black; width: 100%;"><?= $staff ?></span><br>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <?php if(!empty($secondStaffs)){ ?>
                                    <?php foreach($secondStaffs as $staff){ ?>
                                        <span style="display: inline-block; border-bottom: 1px solid black; width: 100%;"><?= $staff ?></span><br>
                                    <?php } ?>
                                <?php } ?>
                                <?php if($doubleStaffEmptyLines > 0){ ?>
                                    <?php for($i = 0; $i < $doubleStaffEmptyLines; $i++){ ?>
                                        <span style="display: inline-block; border-bottom: 1px solid black; width: 100%;">&nbsp;</span><br>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6 col-xs-12">
                    <p><b>RECOMMENDING APPROVAL</b></p>
                    <?php if(!empty($recommenders)){ ?>
                        <?php foreach($recommenders as $recommender){ ?>
                            <span style="display: inline-block; border-bottom: 1px solid black; width: 80%; text-indent: 20px;"><?= $recommender ?></span><br>
                        <?php } ?>
                    <?php } ?>
                    <?php if($recommenderEmptyLines > 0){ ?>
                        <?php for($i = 0; $i < $recommenderEmptyLines; $i++){ ?>
                            <span style="display: inline-block; border-bottom: 1px solid black; width: 80%;">&nbsp;</span><br>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <br>
            <p>2. Per approved Itinerary of Travel, expenses are hereby authorized, subject to availability of funds  and the usual accounting and auditing rules and regulations, chargeable against the fund of the:</p>
            <br>
            <center><span style="display: inline-block; border-bottom: 1px solid black; width: 40%; text-align: center;"><b>NEDA REGIONAL OFFICE 1</b></span></center>
            <br>
            <p>3. Upon completion of the travel, the Certificate of Appearance, Certificate of Travel Completed and a Report on the purpose shall be submitted to the office.</p>
            <br>
            <p style="margin-left: 5rem;"><b>APPROVED:</b></p>
            <br>
            <br>
            <br>
            <center>
                <span style="display: inline-block; border-bottom: 1px solid black; width: 40%; text-align: center;"><b><?= isset($approver[0]) ? $approver[0] : '' ?></b></span>
                <br>
                <?= isset($approver[0]) ? $approver[0] == 'Irenea Ubungen' ? 'OIC-Regional Director' : 'OIC-Assistant Regional Director' : '' ?>
            </center>
        </div>
    </div>
</div>