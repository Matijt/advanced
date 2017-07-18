<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\NumcropHasCompartmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Surfaces planning');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="numcrop-has-compartment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'numcrop_cropnum',
            [
                'attribute'=>'compartment_idCompartment',
                'value'=>'compartmentIdCompartment.compNum',
            ],
            'createDate',
            'freeDate',
            //'lastUpdatedDate',
             'rowsOccupied',
             'rowsLeft',
            [
                'attribute'=>'crop_idcrops',
                'value'=>'cropIdcrops.crop',
            ],

            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view}{update}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-eye-open"></span>', ['value' => Url::to('index.php?r=numcrop-has-compartment%2Fview&numcrop_cropnum='.$model->numcrop_cropnum."&compartment_idCompartment=".$model->compartment_idCompartment), 'class' => 'modalButtonView'], [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    },

                    'update' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span>', ['value' => Url::to('index.php?r=numcrop-has-compartment%2Fupdate&numcrop_cropnum='.$model->numcrop_cropnum."&compartment_idCompartment=".$model->compartment_idCompartment), 'class' => 'modalButtonEdit'], [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    }
                ],
            ],
        ],
    ]); ?>
</div>
