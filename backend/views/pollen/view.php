<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model backend\models\backend\models\Pollen */

$this->title = $model->orderIdorder->fullName;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pollen'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


if($model->harvestDate) {
    $model->harvestDate = date('d-m-Y', strtotime($model->harvestDate));
}
if ($model->useWeek) {
    $model->useWeek = date('d-m-Y', strtotime($model->useWeek));
}
?>

<div class="pollen-view">

    <div class="row">
        <div class="col-sm-9">
            <h2><?= Yii::t('app', 'Pollen').' '. Html::encode($this->title) ?></h2>
        </div>
        <div class="col-sm-3" style="margin-top: 15px">
            
            <?= Html::a(Yii::t('app', 'Update'), ['update', 'idpollen' => $model->idpollen, 'order_idorder' => $model->order_idorder], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'idpollen' => $model->idpollen, 'order_idorder' => $model->order_idorder], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ])
            ?>
        </div>
    </div>

    <div class="row">
<?php 
    $gridColumn = [
        'harvestWeek',
        'harvestDate',
        'harvestMl',
        'useWeek',
        'useMl',
        'youHaveMl',
        [
            'attribute' => 'orderIdorder.fullName',
            'label' => Yii::t('app', 'Order'),
        ],
    ];
    echo DetailView::widget([
        'model' => $model,
        'attributes' => $gridColumn
    ]); 
?>
    </div>
</div>
