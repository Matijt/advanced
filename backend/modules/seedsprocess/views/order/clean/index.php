<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\grid\DataColumn;
use yii\grid\CheckboxColumn;
use yii\widgets\Pjax;
use dosamigos\datepicker\DatePicker;
use backend\models\Order;
use kartik\export\ExportMenu;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Orders');
$this->params['breadcrumbs'][] = "Orders in cleanning process";
?>
<div class="order-index">

    <h1>Orders in cleanning process</h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<?php
// Seeds arrive
$seedsArrive = Order::find()->joinWith(['hybridIdHybr'])->where('order.state = "Harvested plants"')->all();

$date = date('Y-m-d');
$proximaSeamana = date('Y-m-d', strtotime("$date + 7 day"));
$semanaPasada = date('Y-m-d', strtotime("$date - 7 day"));
foreach($seedsArrive AS $item){
    $fechaEvaluada = date('Y-m-d',  strtotime($item->steamDesinfectionF));
    $fechaEvaluadaU = date('Y-m-d',  strtotime($item->steamDesinfectionU));

    $primero = ($fechaEvaluada > $proximaSeamana);
    $segundo = ($fechaEvaluada <= $proximaSeamana && $fechaEvaluada > $date);
    $tercero = ($date >= $fechaEvaluada && $date <= $fechaEvaluadaU);
    $cuarto =  $date > $fechaEvaluadaU;

    switch ($fechaEvaluada) {
        case $primero:
            $item->action = 'You do not have to worry for this order YET.';
            continue;
        case $segundo:
            $item->action = 'You will be cleanning the compartment: '.$item->compartmentIdCompartment->compNum." soon.";
            continue;
        case $tercero:
            $item->action = 'The compartment '.$item->compartmentIdCompartment->compNum." is being cleaned.";
            continue;
        case $cuarto:
            $item->action = 'The compartment '.$item->compartmentIdCompartment->compNum." finished cleanning process.";
            continue;
    }

    $item->save();
};

    Pjax::begin(); ?>    <?php
    $gridColumns = [
        [       'attribute' => 'Crop',
            'value' => 'numCrop'],
        [       'attribute' => 'Hybrid',
            'value' => 'hybridIdHybr.variety'],
        [       'attribute' => 'Compartment',
            'value' => 'compartmentIdCompartment.compNum'],
        'action',
    ];

    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
    ]);
    if(isset($primero) && $primero == 0){
       // $dataProvider->query->andWhere(["like", "state", "Seeds arrive"])->orWhere(["like", "state", "Seeds on its way"]);
        $primero = 1;
    }
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function($model){
                                            if($model->action == "You do not have to worry for this order YET.")
                                            {
                                                return ['class' => 'success', 'style' => 'color: green;'];
                                            }
                                            elseif(stristr($model->action , 'FINISH') !== FALSE){
                                                return ['class' => 'danger', 'style' => 'color: red;'];
                                            }
                                            else{
                                                return ['class' => 'info', 'style' => 'color: blue;'];
                                            }
                                        },
        'columns' => [

//            'idorder',
            'numCrop',
            [
                'attribute'=>'Hybrid_idHybrid',
                'value'=>'hybridIdHybr.variety',
            ],
            [
                'attribute'=>'compartment_idCompartment',
                'value'=>'compartmentIdCompartment.compNum',
            ],
            [
                'attribute'=>'action',
                'value'=>'action',
                'contentOptions'=>[
                'style'=>
                    ['width: 1px;',],
                ],
                'headerOptions' => ['width' => '70'],
                'contentOptions' => [
                    'style'=>'max-width:500px; overflow: auto; word-wrap: break-word;white-space: nowrap;'
                ],
            ],
            // 'numRows',
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
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', 'index.php?r=order/view&id='.$model->idorder."&name=seedsprocess/order/clean", [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    },

                    'update' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'index.php?r=order/update&id='.$model->idorder."&name=seedsprocess/order/clean", [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    },

                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', 'index.php?r=seedsprocess/order/plantingdelete&id='.$model->idorder."&name=clean", [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    }
                ],
            ],

        ],

    ]);
    ?>
<?php Pjax::end(); ?></div>
