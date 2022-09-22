<?php if($type == 'excel'){ ?>
    <style>
        table{
            border-collapse: collapse;
        }
        thead{
            font-size: 12px;
            text-align: center;
        }

        td{
            font-size: 12px;
            border: 1px solid black;
        }

        th{
            text-align: center;
            border: 1px solid black;
        }
    </style>
<?php } ?>
<table class="table table-condensed table-hover table-bordered table-responsive">
    <thead>
        <tr>
            <?php if(!empty($groups)){ ?>
                <?php foreach($groups as $group){ ?>
                    <?php if($group == 'prexc'){ ?>
                        <th rowspan=2>Program Code</th>
                    <?php }else if($group == 'activity'){ ?>
                        <th rowspan=2 colspan=2>PPAs</th>
                    <?php }else if($group == 'objectTitle'){ ?>
                        <th rowspan=2>Object</th>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
            <th rowspan=2>Item</th>
            <th rowspan=2>Mode of Procurement</th>
            <th rowspan=2>End User</th>
            <th rowspan=2>Fund Source</th>
            <th rowspan=2>Estimated Budget</th>
            <th>Quantity</th>
            <?php if($quarters){ ?>
                <?php foreach($quarters as $quarter){ ?>
                    <?php if($months){ ?>
                        <?php foreach($months as $month){ ?>
                            <?php if($quarter->quarter == $month->quarter){ ?>
                                <th colspan=2><?= $month->abbreviation ?></th>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <th colspan=2><?= $quarter->quarter ?></th>
                <?php } ?>
            <?php } ?>
            <th rowspan=2>CSE</th>
        </tr>
        <tr>
            <th>Size</th>
            <?php if($quarters){ ?>
                <?php foreach($quarters as $quarter){ ?>
                    <?php if($months){ ?>
                        <?php foreach($months as $month){ ?>
                            <?php if($quarter->quarter == $month->quarter){ ?>
                                <th>Q</th>
                                <th>C</th>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <th>Q</th>
                    <th>C</th>
                <?php } ?>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
    <?php if(!empty($data)){ ?>
        <?php foreach((array) $data as $firstFilter => $firstLevel){ ?>
            <tr style="font-weight: bold;">
                <td colspan=8><?= $firstFilter ?></td>
                <td align=right><?= number_format($firstLevel['estimatedBudget'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['janQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['janCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['febQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['febCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['marQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['marCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['janQty'] + $firstLevel['febQty'] + $firstLevel['marQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['janCost'] + $firstLevel['febCost'] + $firstLevel['marCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['aprQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['aprCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['mayQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['mayCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['junQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['junCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['aprQty'] + $firstLevel['mayQty'] + $firstLevel['junQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['aprCost'] + $firstLevel['mayCost'] + $firstLevel['junCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['julQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['julCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['augQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['augCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['sepQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['sepCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['julQty'] + $firstLevel['augQty'] + $firstLevel['sepQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['julCost'] + $firstLevel['augCost'] + $firstLevel['sepCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['octQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['octCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['novQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['novCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['decQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['decCost'], 2) ?></td>
                <td align=right><?= number_format($firstLevel['octQty'] + $firstLevel['novQty'] + $firstLevel['decQty'], 0) ?></td>
                <td align=right><?= number_format($firstLevel['octCost'] + $firstLevel['novCost'] + $firstLevel['decCost'], 2) ?></td>
                <td>&nbsp;</td>
            </tr>
            <?php if(!empty($firstLevel) && is_array($firstLevel)){ ?>
                <?php foreach((array) $firstLevel as $secondFilter => $secondLevel){ ?>
                    <?php if(!in_array($secondFilter, $unAllowedIndexes)){ ?>
                    <tr style="font-weight: bold;">
                        <td>&nbsp;</td>
                        <td colspan=7><?= $secondFilter ?></td>
                        <td align=right><?= number_format($secondLevel['estimatedBudget'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['janQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['janCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['febQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['febCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['marQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['marCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['janQty'] + $secondLevel['febQty'] + $secondLevel['marQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['janCost'] + $secondLevel['febCost'] + $secondLevel['marCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['aprQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['aprCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['mayQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['mayCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['junQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['junCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['aprQty'] + $secondLevel['mayQty'] + $secondLevel['junQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['aprCost'] + $secondLevel['mayCost'] + $secondLevel['junCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['julQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['julCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['augQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['augCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['sepQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['sepCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['julQty'] + $secondLevel['augQty'] + $secondLevel['sepQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['julCost'] + $secondLevel['augCost'] + $secondLevel['sepCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['octQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['octCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['novQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['novCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['decQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['decCost'], 2) ?></td>
                        <td align=right><?= number_format($secondLevel['octQty'] + $secondLevel['novQty'] + $secondLevel['decQty'], 0) ?></td>
                        <td align=right><?= number_format($secondLevel['octCost'] + $secondLevel['novCost'] + $secondLevel['decCost'], 2) ?></td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php } ?>
                    <?php if(!empty($secondLevel) && is_array($secondLevel)){ ?>
                        <?php foreach((array) $secondLevel as $thirdFilter => $thirdLevel){ ?>
                            <?php if(!in_array($thirdFilter, $unAllowedIndexes)){ ?>
                            <tr style="font-weight: bold;">
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td colspan=6><?= $thirdFilter ?></td>
                                <td align=right><?= number_format($thirdLevel['estimatedBudget'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['janQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['janCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['febQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['febCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['marQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['marCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['janQty'] + $thirdLevel['febQty'] + $thirdLevel['marQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['janCost'] + $thirdLevel['febCost'] + $thirdLevel['marCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['aprQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['aprCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['mayQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['mayCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['junQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['junCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['aprQty'] + $thirdLevel['mayQty'] + $thirdLevel['junQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['aprCost'] + $thirdLevel['mayCost'] + $thirdLevel['junCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['julQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['julCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['augQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['augCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['sepQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['sepCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['julQty'] + $thirdLevel['augQty'] + $thirdLevel['sepQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['julCost'] + $thirdLevel['augCost'] + $thirdLevel['sepCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['octQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['octCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['novQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['novCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['decQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['decCost'], 2) ?></td>
                                <td align=right><?= number_format($thirdLevel['octQty'] + $thirdLevel['novQty'] + $thirdLevel['decQty'], 0) ?></td>
                                <td align=right><?= number_format($thirdLevel['octCost'] + $thirdLevel['novCost'] + $thirdLevel['decCost'], 2) ?></td>
                                <td>&nbsp;</td>
                            </tr>
                            <?php } ?>
                            <?php if(!empty($thirdLevel) && is_array($thirdLevel)){ ?>
                                <?php foreach((array) $thirdLevel as $fourthFilter => $items){ ?>
                                    <?php if(!in_array($fourthFilter, $unAllowedIndexes)){ ?>
                                    <tr style="font-weight: bold;">
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td colspan=5><?= $fourthFilter ?></td>
                                        <td align=right><?= number_format($items['estimatedBudget'], 2) ?></td>
                                        <td align=right><?= number_format($items['janQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['janCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['febQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['febCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['marQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['marCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['janQty'] + $items['febQty'] + $items['marQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['janCost'] + $items['febCost'] + $items['marCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['aprQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['aprCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['mayQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['mayCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['junQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['junCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['aprQty'] + $items['mayQty'] + $items['junQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['aprCost'] + $items['mayCost'] + $items['junCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['julQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['julCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['augQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['augCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['sepQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['sepCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['julQty'] + $items['augQty'] + $items['sepQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['julCost'] + $items['augCost'] + $items['sepCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['octQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['octCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['novQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['novCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['decQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['decCost'], 2) ?></td>
                                        <td align=right><?= number_format($items['octQty'] + $items['novQty'] + $items['decQty'], 0) ?></td>
                                        <td align=right><?= number_format($items['octCost'] + $items['novCost'] + $items['decCost'], 2) ?></td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <?php } ?>
                                    <?php if(!empty($items['items'])){ ?>
                                        <?php foreach((array) $items['items'] as $item){ ?>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td><?= $item['itemTitle'] ?></td>
                                                <td><?= $item['modeOfProcurement'] ?></td>
                                                <td><?= $item['division'] ?></td>
                                                <td><?= $item['fundSource'] ?></td>
                                                <td><?= number_format($item['totalQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['totalQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['janQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['janQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['febQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['febQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['marQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['marQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format(($item['janQty'] + $item['febQty'] + $item['marQty']), 0) ?></td>
                                                <td align=right><?= number_format(($item['janQty'] + $item['febQty'] + $item['marQty']) * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['aprQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['aprQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['mayQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['mayQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['junQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['junQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format(($item['aprQty'] + $item['mayQty'] + $item['junQty']), 0) ?></td>
                                                <td align=right><?= number_format(($item['aprQty'] + $item['mayQty'] + $item['junQty']) * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['julQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['julQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['augQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['augQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['sepQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['sepQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format(($item['julQty'] + $item['augQty'] + $item['sepQty']), 0) ?></td>
                                                <td align=right><?= number_format(($item['julQty'] + $item['augQty'] + $item['sepQty']) * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['octQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['octQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['novQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['novQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format($item['decQty'], 0) ?></td>
                                                <td align=right><?= number_format($item['decQty'] * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= number_format(($item['octQty'] + $item['novQty'] + $item['decQty']), 0) ?></td>
                                                <td align=right><?= number_format(($item['octQty'] + $item['novQty'] + $item['decQty']) * $item['costPerUnit'], 2) ?></td>
                                                <td align=right><?= $item['cse'] ?></td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    </tbody>
</table>