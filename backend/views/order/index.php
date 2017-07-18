<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use dosamigos\datepicker\DatePicker;
use yii\bootstrap\Modal;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Orders');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]);
    Modal::begin([
        'id' => 'edit',
        'size' => 'modal-lg',
    ]);

    echo "<div id='editContent'></div>";

    Modal::end();
    ?>

    <p>
        <?= Html::button(Yii::t('app', 'Create Order'), ['value' => 'index.php?r=order/create', 'class' => 'modalButtonCreate']) ?>
    </p>
<?php
    Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function($model){
                                            if($model->check == "Great, no problem."){
                                                return ['class' => 'success', 'style' => 'color: green;'];
                                            }else{
                                                return ['class' => 'danger', 'style' => 'color: red;'];
                                            }
                                        },
        'columns' => [
            'numCrop',
            [
                'attribute'=>'Hybrid_idHybrid',
                'value'=>'hybridIdHybr.variety',
            ],
            [
                'attribute'=>'compartment_idCompartment',
                'value'=>'compartmentIdCompartment.compNum',
            ],
            'numRows',
            [
                'attribute' => 'ReqDeliveryDate',
                'value' => 'ReqDeliveryDate',
                'format' => ['date', 'php:d/m/Y'],
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'ReqDeliveryDate',
                    'clientOptions' => [
                        'autoclose' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ])
            ],
            [
                'attribute' => 'sowingDateM',
                'value' => 'sowingDateM',
                'format' => ['date', 'php:d/m/Y'],
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'sowingDateM',
                    'clientOptions' => [
                        'autoclose' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ])
            ],
            [
                'attribute' => 'steamDesinfectionU',
                'value' => 'steamDesinfectionU',
                'format' => ['date', 'php:d/m/Y'],
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'steamDesinfectionU',
                    'clientOptions' => [
                        'autoclose' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ])
            ],
            // 'orderDate',
            // 'contractNumber',
            // 'calculatedYield',
            // 'ssRecDate',
            // 'sowingM',
            // 'sowingF',
            // 'nurseryM',
            // 'nurseryF',
            // 'check',
            // 'sowingDateM',
            // 'sowingDateF',
            // 'realisedNrOfPlantsM',
            // 'realisedNrOfPlantsF',
            // 'transplantingM',
            // 'transplantingF',
            // 'extractedPlantsF',
            // 'extractedPlantsM',
            // 'remainingPlantsF',
            // 'remainingPlantsM',
            // 'pollinationF',
            // 'pollinationU',
            // 'harvestF',
            // 'harvestU',
            // 'steamDesinfectionF',
            // 'steamDesinfectionU',
            // 'remarks:ntext',
            // 'compartment_idCompartment',
            // 'plantingDistance',
            // 'Hybrid_idHybrid',

            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view}{update}{delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-eye-open"></span>', ['value' => Url::to('index.php?r=order%2Fview&id='.$model->idorder), 'class' => 'modalButtonView'], [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    },

                    'update' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span>', ['value' => Url::to('index.php?r=order%2Fupdate&id='.$model->idorder), 'class' => 'modalButtonEdit'], [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    },

                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', 'index.php?r=order/delete&id='.$model->idorder, [
                            'data-method' => 'POST',
                            'title' => Yii::t('app', 'Update'),
                            'data-confirm' => "Are you sure to delete this item?",
                            'role' => 'button',
                            'class' => 'modalButtonDelete',
                        ]);
                    }
                ],
            ],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
