<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\NumcropHasCompartment */

$this->title = "crop #: ".$model->numcrop_cropnum." compartment: ".$model->compartmentIdCompartment->compNum." crop: ".$model->cropIdcrops->crop;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Numcrop Has Compartments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="numcrop-has-compartment-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'numcrop_cropnum',
            'compartment_idCompartment',
            'createDate',
            'freeDate',
//            'lastUpdatedDate',
            'rowsOccupied',
            'rowsLeft',
            'cropIdcrops.crop',
        ],
    ]) ?>

</div>
