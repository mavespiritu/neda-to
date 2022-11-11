<?php if($type != 'pdf'){ ?>
    <style>
    *{ font-family: "Tahoma"; }
    h4{ text-align: center; } 
    p{ font-size: 10px; font-family: "Tahoma";}
    table{
        font-family: "Tahoma";
        border-collapse: collapse;
        width: 100%;
    }
    thead{
        font-size: 12px;
    }

    td{
        font-size: 10px;
        border: 1px solid black;
        padding: 3px 3px;
    }

    th{
        font-size: 10px;
        text-align: center;
        border: 1px solid black;
        padding: 3px 3px;
    }
</style>
<?php } ?>
<h5 class="text-center"><b>
    NEDA REGIONAL OFFICE 1 <br>
    ONLINE PROCUREMENT MANAGEMENT SYSTEM <br>
    Item List as of <?= date("F, j Y") ?>
</b></h5>

<table class="table table-bordered table-responsive table-hover table-condensed">
    <thead>
        <tr>
            <th>#</th>
            <th>DBM Category</th>
            <th>DBM Code</th>
            <th>Title</th>
            <th>Unit of Measure</th>
            <th>Cost Per Unit</th>
            <th>CSE</th>
            <th>Classification</th>
        </tr>
    </thead>
    <tbody>
    <?php $i = 1; ?>
    <?php if(!empty($items)){ ?>
        <?php foreach($items as $item){ ?>
            <tr>
                <td><?= $i ?></td>
                <td><?= $item['dbm_category'] ?></td>
                <td><?= $item['dbm_code'] ?></td>
                <td><?= $item['title'] ?></td>
                <td><?= $item['unit_of_measure'] ?></td>
                <td><?= number_format($item['cost_per_unit'], 2) ?></td>
                <td><?= $item['cse'] ?></td>
                <td><?= $item['classification'] ?></td>
            </tr>
            <?php $i++ ?>
        <?php } ?>
    <?php } ?>
    </tbody>
</table>