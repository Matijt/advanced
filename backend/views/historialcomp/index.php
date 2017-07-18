<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use kartik\daterange\DateRangePicker;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\HistorialcompSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'History of the compartments');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="historialcomp-index">

    <p>
        <?= Html::button(Yii::t('app', 'Create History'), ['value' => Url::to('index.php?r=historialcomp/create'), 'class' => 'btn btn-success modalButtonCreate']) ?>
    </p>

<?php
    $gridColumns = [
            'title',
    'date',
    [
        'attribute' => 'Compartment',
        'value' =>'compartmentIdCompartment.compNum'
    ],
    'content',
    ];

    echo ExportMenu::widget([
            'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'exportConfig' => [
            ExportMenu::FORMAT_TEXT => false,
            ExportMenu::FORMAT_CSV => false,
            ExportMenu::FORMAT_HTML => false,
            ExportMenu::FORMAT_PDF => false,
        ]
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
//                        'timePicker'=>true,
  //                      'timePickerIncrement'=>30,
                        'locale'=>[
                            'format'=>'d-m-Y'
                        ]
                    ]
                ]),
            ],
            [
            'attribute'=>'compartment_idCompartment',
            'value'=>'compartmentIdCompartment.compNum',
            ],

            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-eye-open"></span>', ['value' => 'index.php?r=historialcomp/view&id='.$model->idHistorialcomp, 'class' => 'modalButtonView'], [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    }
                ],
            ],
        ],
    ]); ?>

</div>
