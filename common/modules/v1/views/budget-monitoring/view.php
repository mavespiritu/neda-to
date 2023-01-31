<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;
use yii\web\View;   
use yii\bootstrap\Modal;

function getAreaTree(array $elements, $parentId = null) {
    $branch = array();
    foreach ($elements as $element) {
        if ($element['obj_id'] == $parentId) {
            $children = getAreaTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }
    return empty($branch) ? null : $branch;
}

function getChildren($elements, $padding = 0, $fundSources, $offices, $activity, $stage, $year){
    $data = '';
    if(!empty($elements)){
        foreach ($elements as $element) {
            if(isset($element["children"])){
                $utilizationTotal = [];
                $data.='<tr style="background: #F9F9F9;">';
                
                    $data.='<td style="text-indent: '.$padding.'px; width: 20%;"><b>'.$element['title'].'</b></td>';

                    if($fundSources)
                    {
                        foreach($fundSources as $fundSource)
                        {
                            $data.= $element['source'][$fundSource->code] > 0 ? '<td align=right><b>'.number_format($element['source'][$fundSource->code], 2).'</b></td>' : '<td>&nbsp;</td>';

                            $utilizationTotal[$fundSource->code] = 0;
                        }
                    }

                    if($offices)
                    {
                        foreach($offices as $office)
                        {
                            if($fundSources)
                            {
                                foreach($fundSources as $fundSource)
                                {
                                    $data.= $element['ppmp'][$office->id][$fundSource->code] > 0 ? '<td align=right><b>'.number_format($element['ppmp'][$office->id][$fundSource->code], 2).'</b></td>' : '<td>&nbsp;</td>';

                                    $utilizationTotal[$fundSource->code] += $element['ppmp'][$office->id][$fundSource->code];
                                }
                            }
                        }
                    }

                    if($fundSources)
                    {
                        foreach($fundSources as $fundSource)
                        {
                            $data.= $utilizationTotal[$fundSource->code] > 0 ? '<td align=right><b>'.number_format($utilizationTotal[$fundSource->code], 2).'</b></td>' : '<td>&nbsp;</td>';
                        }
                    }

                    if($fundSources)
                    {
                        foreach($fundSources as $fundSource)
                        {
                            $overUnder = $element['source'][$fundSource->code] - $utilizationTotal[$fundSource->code];
                            $data.= $overUnder != 0 ? $overUnder > 0 ? '<td align=right><b>'.number_format($overUnder, 2).'</b></td>' : '<td align=right style="color: red;"><b>('.number_format(abs($overUnder), 2).')</b></td>' : '<td>&nbsp;</td>';
                        }
                    }

                $data.='</tr>';

                $data.= getChildren($element['children'], intval($padding) + 20, $fundSources, $offices, $activity, $stage, $year);
            }else{
                $utilizationTotal = [];

                $data.='<tr>';
                
                    $data.='<td style="text-indent: '.$padding.'px; width: 20%;">'.$element['title'].'</td>';

                    if($fundSources)
                    {
                        foreach($fundSources as $fundSource)
                        {
                            $data.= $element['source'][$fundSource->code] > 0 ? '<td align=right>'.number_format($element['source'][$fundSource->code], 2).'</td>' : '<td>&nbsp;</td>';

                            $utilizationTotal[$fundSource->code] = 0;
                        }
                    }

                    if($offices)
                    {
                        foreach($offices as $office)
                        {
                            if($fundSources)
                            {
                                foreach($fundSources as $fundSource)
                                {
                                    $data.= $element['ppmp'][$office->id][$fundSource->code] > 0 ? '<td align=right><a style="cursor: pointer;" href="javascript:void(0);" onclick="seeItems('.$office->id.','.$fundSource->id.','.$activity->id.',\''.$stage.'\','.$year.','.$element['id'].');">'.number_format($element['ppmp'][$office->id][$fundSource->code], 2).'</a></td>' : '<td>&nbsp;</td>';

                                    $utilizationTotal[$fundSource->code] += $element['ppmp'][$office->id][$fundSource->code];
                                }
                            }
                        }
                    }

                    if($fundSources)
                    {
                        foreach($fundSources as $fundSource)
                        {
                            $data.= $utilizationTotal[$fundSource->code] > 0 ? '<td align=right>'.number_format($utilizationTotal[$fundSource->code], 2).'</td>' : '<td>&nbsp;</td>';
                        }
                    }

                    if($fundSources)
                    {
                        foreach($fundSources as $fundSource)
                        {
                            $overUnder = $element['source'][$fundSource->code] - $utilizationTotal[$fundSource->code];
                            $data.= $overUnder != 0 ? $overUnder > 0 ? '<td align=right>'.number_format($overUnder, 2).'</td>' : '<td align=right style="color: red;">('.number_format(abs($overUnder), 2).')</td>' : '<td>&nbsp;</td>';
                        }
                    }
                    
                $data.='</tr>';
            }
        }
    }

    return $data;
}
?>

<div class="pull-left">
    <?= ButtonDropdown::widget([
        'label' => 'Export',
        'options' => ['class' => 'btn btn-success'],
        'dropdown' => [
            'items' => [
                ['label' => 'Excel', 'url' => Url::to(['/v1/budget-monitoring/download', 'type' => 'excel', 'post' => json_encode($postData)])],
                ['label' => 'PDF', 'url' => Url::to(['/v1/budget-monitoring/download', 'type' => 'pdf', 'post' => json_encode($postData)])],
            ],
        ],
    ]); ?>
    <?php // Html::a('<i class="fa fa-print"></i> Print', ['#'],['class' => 'btn btn-danger']) ?>
</div>
<div class="clearfix"></div>
<br>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="freeze-table" style="height: 800px;">
        <table class="table table-responsive table-bordered table-hover" style="min-width: 2000px;">
            <thead>
                <tr>
                    <th rowspan=3 style="width: 10%;">Objects</th>
                    <th colspan=2><?= $activity->pap->codeTitle ?></th>
                    <?php if($offices){ ?>
                        <?php foreach($offices as $office){ ?>
                            <th colspan=2 rowspan=2><?= $office->abbreviation ?></th>
                        <?php } ?>
                    <?php } ?>
                    <th colspan=2 rowspan=2>Total Utilization</th>
                    <th colspan=2 rowspan=2>(Over) Under</th>
                </tr>
                <tr>
                    <th colspan=2><?= $activity->pap->codeAndTitle ?></th>
                </tr>
                <tr>
                    <?php for($i = 0; $i < (count($offices) + 3); $i++){ ?>
                        <?php if($fundSources){ ?>
                            <?php foreach($fundSources as $fundSource){ ?>
                                <th><?= $fundSource->code ?></th>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <tr style="background: #F9F9F9;">
                    <?php $grandUtilizationTotal = []; ?>
                    <td><b>Total</b></td>
                    <?php if(!empty($total)){ ?>
                        <?php if($fundSources){ ?>
                            <?php foreach($fundSources as $fundSource){ ?>
                                <td align=right><b><?= $total['source'][$fundSource->code] > 0 ? number_format($total['source'][$fundSource->code], 2) : '' ?></td>
                                <?php $grandUtilizationTotal[$fundSource->code] = 0; ?>
                            <?php } ?>
                        <?php } ?>

                        <?php if($offices){ ?>
                            <?php foreach($offices as $office){ ?>
                                <?php if($fundSources){ ?>
                                    <?php foreach($fundSources as $fundSource){ ?>
                                        <td align=right><b><?= $total['ppmp'][$office->id][$fundSource->code] > 0 ? number_format($total['ppmp'][$office->id][$fundSource->code], 2) : '' ?></td>
                                        <?php $grandUtilizationTotal[$fundSource->code] += $total['ppmp'][$office->id][$fundSource->code]; ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                        
                        <?php if($fundSources){ ?>
                            <?php foreach($fundSources as $fundSource){ ?>
                                <?= $grandUtilizationTotal[$fundSource->code] > 0 ? '<td align=right><b>'.number_format($grandUtilizationTotal[$fundSource->code], 2).'</b></td>' : '<td>&nbsp;</td>'; ?>
                            <?php } ?>
                        <?php } ?>

                        <?php if($fundSources){ ?>
                            <?php foreach($fundSources as $fundSource){ ?>
                                <?php $overUnder = $total['source'][$fundSource->code] - $grandUtilizationTotal[$fundSource->code]; ?>
                                <?= $overUnder != 0 ? $overUnder > 0 ? '<td align=right><b>'.number_format($overUnder, 2).'</b></td>' : '<td align=right style="color: red;"><b>('.number_format(abs($overUnder), 2).')</b></td>' : '<td>&nbsp;</td>'; ?>
                            <?php } ?>
                        <?php } ?>    
                    <?php } ?>
                </tr>
                <?= getChildren(getAreaTree($data, null), "", $fundSources, $offices, $activity, $stage, $year) ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php
  Modal::begin([
    'id' => 'modal',
    'size' => "modal-lg",
    'header' => '<div id="modal-header"><h4>View Items</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="modal-content"></div>';
  Modal::end();
?>
<?php
  $script = '
    function seeItems(office_id, fund_source_id, activity_id, stage, year, obj_id)
    {
        $("#modal").modal("show").find("#modal-content").load("'.Url::to(['/v1/budget-monitoring']).'" + "/view-items?office_id=" + office_id + "&fund_source_id=" + fund_source_id + "&activity_id=" + activity_id + "&stage=" + stage + "&year=" + year + "&obj_id=" + obj_id);
    }

    $(document).ready(function() {
        $(".freeze-table").freezeTable({
            "scrollable": true,
            "columnNum" : 1
        });
    });
  ';
  $this->registerJs($script, View::POS_END);
?>