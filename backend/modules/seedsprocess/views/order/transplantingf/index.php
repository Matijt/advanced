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
$this->params['breadcrumbs'][] = "Transplanting orders Female";
?>
<div class="order-index">

    <h1>Transplanting orders Female</h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<?php
// Seeds arrive
$seedsArrive = Order::find()->joinWith(['hybridIdHybr'])->where('order.state = "Male plants just transplanted"')->all();

$date = date('Y-m-d');
$proximaSeamana = date('Y-m-d', strtotime("$date + 7 day"));
$semanaPasada = date('Y-m-d', strtotime("$date - 7 day"));
foreach($seedsArrive AS $item){
    $fechaEvaluada = date('Y-m-d',  strtotime($item->transplantingF));

    $primero = ($fechaEvaluada > $proximaSeamana);
    $segundo = ($fechaEvaluada <= $proximaSeamana && $fechaEvaluada > $date);
    $tercero = $fechaEvaluada === $date;
    $cuarto = $fechaEvaluada < $date && $fechaEvaluada >= $semanaPasada;
    $quinto = $fechaEvaluada < $semanaPasada;

    switch ($fechaEvaluada) {
        case $primero:
            $item->action = 'You do not have to worry for this order YET.';
            continue;
        case $segundo:
            $item->action = 'Remember you should transplant '.$item->hybridIdHybr->motherIdMother->variety.' to the compartment '.$item->compartmentIdCompartment->compNum.' In the next days';
            continue;
        case $tercero:
            $item->action = 'Transplant '.$item->sowingF.' '.$item->hybridIdHybr->motherIdMother->variety.' to the compartment '.$item->compartmentIdCompartment->compNum.' TODAY';
            continue;
        case $cuarto:
            $item->action = 'YOU SHOULD HAVE TRANSPLANTED '.$item->sowingF.' '.$item->hybridIdHybr->motherIdMother->variety.' to the compartment '.$item->compartmentIdCompartment->compNum." already";
            continue;
        case $quinto:
            $item->action = 'YOU REQUIRED TO TRANSPLANT '.$item->sowingF.' '.$item->hybridIdHybr->motherIdMother->variety.' TO THE COMPARTMENT '.$item->compartmentIdCompartment->compNum.' ON THE DAY '.$fechaEvaluada." IT IS LATE";
            continue;
    }

    $item->save();
};

    Pjax::begin(); ?>    <?php
    $gridColumns = [
        [       'attribute' => 'Crop',
            'value' => 'numCrop'],
        [       'attribute' => 'Mother',
            'value' => 'hybridIdHybr.motherIdMother.variety'],
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
                                            elseif(stristr($model->action , 'Remember you should plant') !== FALSE){
                                                return ['class' => 'info', 'style' => 'color: blue;'];
                                            }
                                            elseif(stristr($model->action , 'TODAY') !== FALSE){
                                                    return ['style' => 'background-color: #1fc202; color: #0b5200;'];
                                            }
                                            else{
                                                return ['class' => 'danger', 'style' => 'color: red;'];
                                            }
                                        },
        'columns' => [

//            'idorder',
            'numCrop',
            [
                'attribute'=> "prueba2",
                'value'=>'hybridIdHybr.motherIdMother.variety',
            ],
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
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', 'index.php?r=order/view&id='.$model->idorder."&name=seedsprocess/order/transplantingf", [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    },

                    'update' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'index.php?r=order/update&id='.$model->idorder."&name=seedsprocess/order/transplantingf", [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    },

                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', 'index.php?r=seedsprocess/order/plantingdelete&id='.$model->idorder."&name=transplantingf", [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    }
                ],
            ],

        ],

    ]);
    ?>
<?php Pjax::end(); ?></div>
