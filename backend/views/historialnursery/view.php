<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Historialnursery */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'History of the Nurseries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="historialnursery-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            'content:ntext',
            'date',
            'nurseryIdnursery.numcompartment',
        ],
    ]) ?>

</div>
