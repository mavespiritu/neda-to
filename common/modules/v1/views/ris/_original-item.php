<div id="alert-container"></div>
<div class="row">
    <div class="col-md-12 col-xs-12">
        <?= $this->render('_original-item-form', [
            'model' => $model,
            'appropriationItemModel' => $appropriationItemModel,
            'activities' => $activities,
            'subActivities' => $subActivities,
            'items' => $items,
            'months' => $months,
        ]) ?>
        <br>
        <p class="panel-title"><i class="fa fa-list"></i> Available PPMP Items</p><br>
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div id="ris-item-list">
                    <p class="text-center">No items selected.</p>
                </div>
            </div>
        </div>
    </div>
</div>
