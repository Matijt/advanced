<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Father */

$this->title = $model->variety;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Males'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="father-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'variety',
            'steril',
            'germination',
            'tsw',
            'remarks:ntext',
        ],
    ]) ?>

</div>
