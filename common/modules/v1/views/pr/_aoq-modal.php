<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
use yii\bootstrap\Collapse;
?>

<?= !empty($items) ? Collapse::widget(['items' => $items, 'encodeLabels' => false, 'autoCloseItems' => true, 'options' => ['id' => 'aoq-modal-view-content']]) : 'No AOQs' ?>