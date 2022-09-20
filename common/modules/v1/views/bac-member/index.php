<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\BacMemberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'BAC Members';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bac-member-index">
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <table class="table table-bordered table-responsive">
                <tr>
                    <th colspan=3 style="width: 30%;">Chairperson</th>
                    <td><?= $chairName ? $chairName->name : '' ?></td>
                    <td><?= Html::a('Change', ['/v1/bac-member/bac', 'title' => 'BAC Chairperson'], ['class' => 'btn btn-primary btn-block btn-xs']) ?></td>
                </tr>
                <tr>
                    <th colspan=3>Vice-Chairperson</th>
                    <td><?= $viceChairName ? $viceChairName->name : '' ?></td>
                    <td><?= Html::a('Change', ['/v1/bac-member/bac', 'title' => 'BAC Vice-Chairperson'], ['class' => 'btn btn-primary btn-block btn-xs']) ?></td>
                </tr>
                <tr>
                    <th colspan=3>Member</th>
                    <td><?= $memberName ? $memberName->name : '' ?></td>
                    <td><?= Html::a('Change', ['/v1/bac-member/bac', 'title' => 'BAC Vice-Chairperson'], ['class' => 'btn btn-primary btn-block btn-xs']) ?></td>
                </tr>
                <tr>
                    <th colspan=4>Provisional Members with Technical Expertise</th>
                </tr>
                <?php if(!empty($expertMembers)){ ?>
                    <?php foreach($expertMembers as $expertise => $subExpertMembers){ ?>
                        <?php if(gettype($subExpertMembers) == 'array'){ ?>
                            <tr>
                                <td colspan=3 style="text-indent: 20px;"><b><?= $expertise ?></b></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <?php foreach($subExpertMembers as $subExpertise => $subExpertMember){ ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td align=right colspan=2 style="width: 20%"><?= $subExpertise ?></td>
                                    <td><?= !empty($subExpertMember) ? $subExpertMember->name : '' ?></td>
                                    <td><?= Html::a('Change', ['/v1/bac-member/expert', 'expertise' => $expertise, 'sub_expertise' => $subExpertise], ['class' => 'btn btn-primary btn-block btn-xs']) ?></td>
                                </tr>
                            <?php } ?>
                        <?php }else{ ?>
                            <tr>
                                <td colspan=3 style="text-indent: 20px;"><b><?= $expertise ?></b></td>
                                <td><?= !empty($subExpertMembers) ? $subExpertMembers->name : '' ?></td>
                                <td><?= Html::a('Change', ['/v1/bac-member/expert', 'expertise' => $expertise, 'sub_expertise' => ''], ['class' => 'btn btn-primary btn-block btn-xs']) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
                <tr>
                    <th colspan=4>Provisional Members - End User</th>
                </tr>
                <?php if(!empty($divisionMembers)){ ?>
                    <?php foreach($divisionMembers as $division => $divisionMember){ ?>
                        <tr>
                            <td colspan=3 align=right><?= $division ?></td>
                            <td><?= !empty($divisionMember) ? $divisionMember->name : '' ?></td>
                            <td><?= Html::a('Change', ['/v1/bac-member/end-user', 'office_id' => $division], ['class' => 'btn btn-primary btn-block btn-xs']) ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </table>
        </div>
        <div class="col-md-6 col-xs-12">
            <div id="bac-form"></div>
        </div>
    </div>
</div>
