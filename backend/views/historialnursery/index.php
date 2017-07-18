<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use kartik\daterange\DateRangePicker;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\HistorialnurserySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'History of the Nurseries');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="historialnursery-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::button(Yii::t('app', 'Create History of the Nurseries'), ['value' => Url::to('index.php?r=historialnursery/create'),'class' => 'btn btn-success modalButtonCreate']) ?>
    </p>

    <?php
    $gridColumns = [
        'title',
        'date',
        [
            'attribute' => 'Nursery compartment',
            'value' =>'nurseryIdnursery.numcompartment'
        ],
        'content',
    ];

    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
    ])
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'title',
            [
                'attribute' => 'date',
                'value' => 'date',
                'format' => ['date', 'php:d/m/Y'],
                'filter' => DateRangePicker::widget([
                    'model'=>$searchModel,
                    'attribute'=>'date',
                    'convertFormat'=>true,
                    'pluginOptions'=>[
                        'autoclose' => true,
                        'locale'=>[
                        'format'=>'d-m-Y'
                        ]
                    ]
                ]),
            ],
            [
                'attribute'=>'nursery_idnursery',
                'value'=>'nurseryIdnursery.numcompartment',
            ],

            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-eye-open"></span>', [ 'value' => Url::to("index.php?r=historialnursery/view&id=".$model->idhistorialnursery), 'class' => 'modalButtonView'], [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    }
                ],
            ],
        ],
    ]); ?>
</div>
