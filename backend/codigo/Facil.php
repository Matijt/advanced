<?php

namespace backend\codigo;
/**
 * Created by PhpStorm.
 * User: Matias
 * Date: 13/03/2017
 * Time: 08:52 AM
 */

use backend\models\Histcrop;
use Yii;
use backend\models\NumcropHasCompartment;
use backend\models\Numcrop;
use backend\modules\seedsprocess\models\Crop;
use backend\modules\seedsprocess\models\Order;
use backend\modules\seedsprocess\models\Mother;
use backend\modules\seedsprocess\models\Germination;
use backend\modules\seedsprocess\models\OrderSearch;
use backend\models\OrderSearchm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class Facil{


    public function editar($model, $models){

        // número de crop
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("SELECT MAX(numcrop_cropnum) AS actualCrop, compartment_idCompartment AS comp,
                                                   freeDate, rowsOccupied, rowsLeft FROM numcrop_has_compartment WHERE compartment_idCompartment = :compartment", [':compartment' => $model->compartment_idCompartment]);
        $query = $command->queryAll();
        $actualcrop = ArrayHelper::getValue($query, '0');
        $actualcrop = ArrayHelper::getValue($actualcrop, 'actualCrop');
        // fechas

        $F1 = $model->hybridIdHybr->cropIdcrops->sowingFemale;
        $TM = $model->hybridIdHybr->cropIdcrops->transplantingMale;
        $TF = $model->hybridIdHybr->cropIdcrops->transplantingFemale;
        $PF = $model->hybridIdHybr->cropIdcrops->pollinitionF;
        $PU = $model->hybridIdHybr->cropIdcrops->pollinitionU;
        $HF = $model->hybridIdHybr->cropIdcrops->harvestF;
        $HU = $model->hybridIdHybr->cropIdcrops->harvestU;
        $SDA = $model->hybridIdHybr->cropIdcrops->steamDesinfection;

        $model->ReqDeliveryDate = date('Y-m-d', strtotime($model->ReqDeliveryDate));
        $model->orderDate = date('Y-m-d', strtotime($model->orderDate));
        $model->ssRecDate = date('Y-m-d', strtotime($model->ssRecDate));
        $model->sowingDateM = date('Y-m-d', strtotime($model->sowingDateM));
        $model->sowingDateF = date('Y-m-d', strtotime($model->sowingDateF));
        $model->transplantingM = date('Y-m-d', strtotime($model->transplantingM));
        $model->transplantingF = date('Y-m-d', strtotime($model->transplantingF));
        $model->pollenColectF = date('Y-m-d', strtotime("$model->sowingDateF + " . $TF . " day"));
        $model->pollenColectU = date('Y-m-d', strtotime("$model->pollenColectF + " . 112 . " day"));
        $model->pollinationF = date('Y-m-d', strtotime($model->pollinationF));
        $model->pollinationU = date('Y-m-d', strtotime($model->pollinationU));
        $model->harvestF = date('Y-m-d', strtotime($model->harvestF));
        $model->harvestU = date('Y-m-d', strtotime($model->harvestU));
        $model->steamDesinfectionF = date('Y-m-d', strtotime($model->steamDesinfectionF));
        $model->steamDesinfectionU = date('Y-m-d', strtotime($model->steamDesinfectionU));
        $dates = new \DateTime($model->sowingDateM);
        $datem = new \DateTime($models->sowingDateM);
        $intento = Order::find()->joinWith(['hybridIdHybr'])->where('(order.idorder = :id)', ['id' =>  $model->idorder])->all();

        if(!($model->numRows == $models->numRows)){
            $germinationM = $model->hybridIdHybr->motherIdMother->germination;
            $germinationF = $model->hybridIdHybr->fatherIdFather->germination;

            if($model->numRows <= 4){
                $ratio = 4;
            }else{
                $ratio = 5.25;
            }

            $model->netNumOfPlantsF = round((((3775/$model->plantingDistance)*$ratio)/(1+$ratio))*$model->numRows);
            $model->netNumOfPlantsM = round((((3775/$model->plantingDistance))/(1+$ratio))*$model->numRows);
            $model->sowingF = ($model->netNumOfPlantsF/$germinationM)*100;
            $model->sowingM = ($model->netNumOfPlantsM/$germinationF)*100;

            $model->sowingF = round($model->sowingF);
            $model->sowingM = round($model->sowingM);
            $model->nurseryF = round(($model->netNumOfPlantsF) * 1.15);
            $model->nurseryM = round(($model->netNumOfPlantsM) * 1.15);
            if ($model->hybridIdHybr->motherIdMother->steril == 50) {
                $model->sowingF = ($model->sowingF) * 2;
                $model->nurseryF = ($model->nurseryF) * 2;
            }
            if ($model->hybridIdHybr->fatherIdFather->steril == 50) {
                $model->nurseryM = ($model->nurseryM) * 2;
                $model->sowingM = ($model->sowingM) * 2;
            }
        }
        $model->calculatedYield = ($model->netNumOfPlantsF*$model->hybridIdHybr->motherIdMother->gP)/1000;

        $cropUse = $model->numCrop;

        if(!($model->numCrop == $models->numCrop)){

            $cropEdit = NumcropHasCompartment::find()->where('(numcrop_cropnum = :crop) AND compartment_idCompartment = :comp', ['crop' =>  ($model->numCrop-1), 'comp' => $model->compartmentIdCompartment->idCompartment])->all();

            foreach ($cropEdit as$item) {
                $model->sowingDateM = date('Y-m-d', strtotime("$item->freeDate - " . (($model->hybridIdHybr->cropIdcrops->transplantingMale) - 1) . " day"));
            }
            $model->hybridIdHybr->save();
            $item = $model;

//          "Evaluación de la fecha para ver si es en invierno.";
            $mes = date('n', strtotime($item->sowingDateM));
            $dia = date('j', strtotime($item->sowingDateM));

            if (($mes <= 3)) {
                $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM + 7 day"));
                $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + 7 day"));
                if ($mes == 3 && $dia > 10) {
                    $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM - 7 day"));
                    $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF - 7 day"));
                }
            } elseif (($mes == 12)) {
                if ($dia > 10) {
                    $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM + 7 day"));
                    $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + 7 day"));
                }
            }


            // Cambiar los valores


            foreach ($intento AS $item) {
                $item->sowingDateM = date('Y-m-d', strtotime($model->sowingDateM));
                $item->sowingDateF = date('Y-m-d', strtotime("$model->sowingDateM + " . ($model->hybridIdHybr->sowingFemale + $model->hybridIdHybr->cropIdcrops->sowingFemale) . " day"));
                $item->transplantingM = date('Y-m-d', strtotime("$model->sowingDateM+ " . ($model->hybridIdHybr->transplantingMale + $model->hybridIdHybr->cropIdcrops->transplantingMale) . " day"));
                $item->transplantingF = date('Y-m-d', strtotime("$item->sowingDateF+ " . ($model->hybridIdHybr->transplantingFemale + $model->hybridIdHybr->cropIdcrops->transplantingFemale) . " day"));
                if (($mes <= 3)) {
                    $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM + 7 day"));
                    $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + 7 day"));
                    if ($mes == 3 && $dia > 10) {
                        $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM - 7 day"));
                        $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF - 7 day"));
                    }
                } elseif (($mes == 12)) {
                    if ($dia > 10) {
                        $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM + 7 day"));
                        $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + 7 day"));
                    }
                }

                $item->pollinationF = date('Y-m-d', strtotime("$item->transplantingF + " . ($model->hybridIdHybr->pollinitionF + $model->hybridIdHybr->cropIdcrops->pollinitionF) . " day"));
                $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationF + " . ($model->hybridIdHybr->pollinitionU + $model->hybridIdHybr->cropIdcrops->pollinitionU) . " day"));
                $item->harvestF = date('Y-m-d', strtotime("$item->pollinationF + " . ($model->hybridIdHybr->harvestF + $model->hybridIdHybr->cropIdcrops->harvestF) . " day"));
                $item->harvestU = date('Y-m-d', strtotime("$item->harvestF + " . ($model->hybridIdHybr->harvestU + $model->hybridIdHybr->cropIdcrops->harvestU) . " day"));
                $item->steamDesinfectionF = $item->harvestU;
                $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionF + " . ($model->hybridIdHybr->steamDesinfection + $model->hybridIdHybr->cropIdcrops->steamDesinfection) . " day"));
                $item->pollenColectF = date('Y-m-d', strtotime("$item->transplantingF + " . (14+$model->hybridIdHybr->pollenColectF) . " day"));
                $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . (112+$model->hybridIdHybr->pollenColectU) . " day"));
                $item->save();
            }

        }
            if(!($model->sowingDateM == $models->sowingDateM)) {
            $modelsdateM = new \DateTime($models->sowingDateM);
            $modeldateM = new \DateTime($model->sowingDateM);
            $dias = (strtotime($models->sowingDateM) > strtotime($model->sowingDateM));
            $dias = abs($dias);
            $dias = floor($dias);
            $diffF = date_diff($modelsdateM, $modeldateM);
            $diffF = $diffF->format("%d");
            $diffF = round($diffF);

            $model->hybridIdHybr->save();
            $item = $model;

//          "Evaluación de la fecha para ver si es en invierno.";
            $mes = date('n', strtotime($item->sowingDateM));
            $dia = date('j', strtotime($item->sowingDateM));

            if (($mes <= 3)) {
                $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM + 7 day"));
                $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + 7 day"));
                if ($mes == 3 && $dia > 10) {
                    $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM - 7 day"));
                    $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF - 7 day"));
                }
            } elseif (($mes == 12)) {
                if ($dia > 10) {
                    $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM + 7 day"));
                    $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + 7 day"));
                }
            }

            // Cambiar los valores

            foreach ($intento AS $item) {
                $modelH = new Histcrop();
                $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                if ($dias == 1) {
                    $item->sowingDateF = date('Y-m-d', strtotime("$item->sowingDateM + " . (-$diffF + $model->hybridIdHybr->sowingFemale + $model->hybridIdHybr->cropIdcrops->sowingFemale) . " day"));
                    $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM - " . ($diffF + $model->hybridIdHybr->transplantingMale) . " day"));
                    $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF - " . ($diffF + $model->hybridIdHybr->transplantingFemale) . " day"));
                    $item->pollinationF = date('Y-m-d', strtotime("$item->pollinationF - " . ($diffF + $model->hybridIdHybr->pollinitionF) . " day"));
                    $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU - " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                    $item->harvestF = date('Y-m-d', strtotime("$item->harvestF - " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                    $item->harvestU = date('Y-m-d', strtotime("$item->harvestU - " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                    $item->steamDesinfectionF = $item->harvestU;
                    $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU - " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                    $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                    $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                    $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - $diffF;
                } else {
                    $item->sowingDateF = date('Y-m-d', strtotime("$item->sowingDateM + " . ($diffF + $model->hybridIdHybr->sowingFemale + $model->hybridIdHybr->cropIdcrops->sowingFemale) . " day"));
                    $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM + " . ($diffF + $model->hybridIdHybr->transplantingMale) . " day"));
                    $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + " . ($diffF + $model->hybridIdHybr->transplantingFemale) . " day"));
                    $item->pollinationF = date('Y-m-d', strtotime("$item->pollinationF + " . ($diffF + $model->hybridIdHybr->pollinitionF) . " day"));
                    $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU + " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                    $item->harvestF = date('Y-m-d', strtotime("$item->harvestF + " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                    $item->harvestU = date('Y-m-d', strtotime("$item->harvestU + " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                    $item->steamDesinfectionF = $item->harvestU;
                    $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU + " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                    $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                    $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                    $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                }

                $modelH->proces = "Siembra de hembra.";
                $item->save();
                $modelH->save();

            }
        }else{

                if($models->sowingDateF != $model->sowingDateF) {
                    $modelsdateF = new \DateTime($models->sowingDateF);
                    $modeldateF = new \DateTime($model->sowingDateF);
                    $dias = (strtotime($models->sowingDateF) > strtotime($model->sowingDateF));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF);



                    if ($dias == 1) {
                        $model->hybridIdHybr->sowingFemale = ($model->hybridIdHybr->sowingFemale) - $diffF;
                    } else {
                        $model->hybridIdHybr->sowingFemale = $model->hybridIdHybr->sowingFemale + $diffF;
                    }
                    $model->hybridIdHybr->save();

                    foreach($intento AS $item){
                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF - " . ($diffF + $model->hybridIdHybr->transplantingFemale) . " day"));
                            $item->pollinationF = date('Y-m-d', strtotime("$item->pollinationF - " . ($diffF + $model->hybridIdHybr->pollinitionF) . " day"));
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU - " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF - " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU - " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU - " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - $diffF;
                        } else {
                            $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + " . ($diffF + $model->hybridIdHybr->transplantingFemale) . " day"));
                            $item->pollinationF = date('Y-m-d', strtotime("$item->pollinationF + " . ($diffF + $model->hybridIdHybr->pollinitionF) . " day"));
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU + " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF + " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU + " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU + " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
                elseif($models->transplantingM != $model->transplantingM) {
                    $modelsdateF = new \DateTime($models->transplantingM);
                    $modeldateF = new \DateTime($model->transplantingM);
                    $dias = (strtotime($models->transplantingM) > strtotime($model->transplantingM));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF);
                    if ($dias == 1) {
                        $model->hybridIdHybr->transplantingMale = ($model->hybridIdHybr->transplantingMale) - $diffF;
                    } else {
                        $model->hybridIdHybr->transplantingMale = $model->hybridIdHybr->transplantingMale + $diffF;
                    }

                    foreach($intento AS $item){
                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM - " . ($diffF + $model->hybridIdHybr->transplantingMale) . " day"));
                            $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF - " . ($diffF + $model->hybridIdHybr->transplantingFemale) . " day"));
                            $item->pollinationF = date('Y-m-d', strtotime("$item->pollinationF - " . ($diffF + $model->hybridIdHybr->pollinitionF) . " day"));
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU - " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF - " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU - " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU - " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - ($diffF + $model->hybridIdHybr->sowingFemale);
                        } else {
                            $item->transplantingM = date('Y-m-d', strtotime("$item->transplantingM + " . ($diffF + $model->hybridIdHybr->transplantingMale) . " day"));
                            $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + " . ($diffF + $model->hybridIdHybr->transplantingFemale) . " day"));
                            $item->pollinationF = date('Y-m-d', strtotime("$item->pollinationF + " . ($diffF + $model->hybridIdHybr->pollinitionF) . " day"));
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU + " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF + " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU + " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
                elseif($models->transplantingF != $model->transplantingF) {
                    $modelsdateF = new \DateTime($models->transplantingF);
                    $modeldateF = new \DateTime($model->transplantingF);
                    $dias = (strtotime($models->transplantingF) > strtotime($model->transplantingF));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF);
                    if ($dias == 1) {
                        $model->hybridIdHybr->transplantingMale = ($model->hybridIdHybr->transplantingMale) - $diffF;
                    } else {
                        $model->hybridIdHybr->transplantingMale = $model->hybridIdHybr->transplantingMale + $diffF;
                    }

                    foreach($intento AS $item){

                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF - " . ($diffF + $model->hybridIdHybr->transplantingFemale) . " day"));
                            $item->pollinationF = date('Y-m-d', strtotime("$item->pollinationF - " . ($diffF + $model->hybridIdHybr->pollinitionF) . " day"));
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU - " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF - " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU - " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU - " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - ($diffF + $model->hybridIdHybr->sowingFemale);
                        } else {
                            $item->transplantingF = date('Y-m-d', strtotime("$item->transplantingF + " . ($diffF + $model->hybridIdHybr->transplantingFemale) . " day"));
                            $item->pollinationF = date('Y-m-d', strtotime("$item->pollinationF + " . ($diffF + $model->hybridIdHybr->pollinitionF) . " day"));
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU + " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF + " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU + " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU + " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
                elseif($models->pollinationF != $model->pollinationF) {
                    $modelsdateF = new \DateTime($models->pollinationF);
                    $modeldateF = new \DateTime($model->pollinationF);
                    $dias = (strtotime($models->pollinationF) > strtotime($model->pollinationF));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF);
                    if ($dias == 1) {
                        $model->hybridIdHybr->transplantingMale = ($model->hybridIdHybr->transplantingMale) - $diffF;
                    } else {
                        $model->hybridIdHybr->transplantingMale = $model->hybridIdHybr->transplantingMale + $diffF;
                    }


                    foreach($intento AS $item){

                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU - " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF - " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU - " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU - " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - ($diffF + $model->hybridIdHybr->sowingFemale);
                        } else {
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU + " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF + " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU + " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU + " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
                elseif($models->pollinationU != $model->pollinationU) {
                    $modelsdateF = new \DateTime($models->pollinationU);
                    $modeldateF = new \DateTime($model->pollinationU);
                    $dias = (strtotime($models->pollinationU) > strtotime($model->pollinationU));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF);
                    if ($dias == 1) {
                        $model->hybridIdHybr->transplantingFemale = ($model->hybridIdHybr->transplantingFemale) - $diffF;
                    } else {
                        $model->hybridIdHybr->transplantingFemale = $model->hybridIdHybr->transplantingFemale + $diffF;
                    }

                    foreach($intento AS $item){

                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU - " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF - " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU - " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU - " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - ($diffF + $model->hybridIdHybr->sowingFemale);
                        } else {
                            $item->pollinationU = date('Y-m-d', strtotime("$item->pollinationU + " . ($diffF + $model->hybridIdHybr->pollinitionU) . " day"));
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF + " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU + " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU + " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
                elseif($models->harvestF != $model->harvestF) {
                    $modelsdateF = new \DateTime($models->harvestF);
                    $modeldateF = new \DateTime($model->harvestF);
                    $dias = (strtotime($models->harvestF) > strtotime($model->harvestF));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF);
                    if ($dias == 1) {
                        $model->hybridIdHybr->pollinitionF = ($model->hybridIdHybr->pollinitionF) - $diffF;
                    } else {
                        $model->hybridIdHybr->pollinitionF = $model->hybridIdHybr->pollinitionF + $diffF;
                    }


                    foreach($intento AS $item){

                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF - " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU - " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU - " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - ($diffF + $model->hybridIdHybr->sowingFemale);
                        } else {
                            $item->harvestF = date('Y-m-d', strtotime("$item->harvestF + " . ($diffF + $model->hybridIdHybr->harvestF) . " day"));
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU + " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU + " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
                elseif($models->harvestU != $model->harvestU) {
                    $modelsdateF = new \DateTime($models->harvestU);
                    $modeldateF = new \DateTime($model->harvestU);
                    $dias = (strtotime($models->harvestU) > strtotime($model->harvestU));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF);
                    if ($dias == 1) {
                        $model->hybridIdHybr->pollinitionU = ($model->hybridIdHybr->pollinitionU) - $diffF;
                    } else {
                        $model->hybridIdHybr->pollinitionU = $model->hybridIdHybr->pollinitionU + $diffF;
                    }

                    foreach($intento AS $item){

                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU - " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU - " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - ($diffF + $model->hybridIdHybr->sowingFemale);
                        } else {
                            $item->harvestU = date('Y-m-d', strtotime("$item->harvestU + " . ($diffF + $model->hybridIdHybr->harvestU) . " day"));
                            $item->steamDesinfectionF = $item->harvestU;
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU + " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
                elseif($models->steamDesinfectionU != $model->steamDesinfectionU) {
                    $modelsdateF = new \DateTime($models->steamDesinfectionU);
                    $modeldateF = new \DateTime($model->steamDesinfectionU);
                    $dias = (strtotime($models->steamDesinfectionU) > strtotime($model->steamDesinfectionU));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF);
                    if ($dias == 1) {
                        $model->hybridIdHybr->harvestF = ($model->hybridIdHybr->harvestF) - $diffF;
                    } else {
                        $model->hybridIdHybr->harvestF = $model->hybridIdHybr->harvestF + $diffF;
                    }

                    foreach($intento AS $item){

                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU - " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - ($diffF + $model->hybridIdHybr->sowingFemale);
                        } else {
                            $item->steamDesinfectionU = date('Y-m-d', strtotime("$item->steamDesinfectionU + " . ($diffF + $model->hybridIdHybr->steamDesinfection) . " day"));
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
                elseif($models->pollenColectF != $model->pollenColectF) {
                    $modelsdateF = new \DateTime($models->pollenColectF);
                    $modeldateF = new \DateTime($model->pollenColectF);
                    $dias = (strtotime($models->pollenColectF) > strtotime($model->pollenColectF));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF/2);

                    foreach($intento AS $item){

                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF - " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - ($diffF + $model->hybridIdHybr->steamDesinfection);
                        } else {
                            $item->pollenColectF = date('Y-m-d', strtotime("$item->pollenColectF + " . ($diffF + $model->hybridIdHybr->pollenColectF) . " day"));
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
                elseif($models->pollenColectU != $model->pollenColectU) {
                    $modelsdateF = new \DateTime($models->pollenColectU);
                    $modeldateF = new \DateTime($model->pollenColectU);
                    $dias = (strtotime($models->pollenColectU) > strtotime($model->pollenColectU));
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $diffF = date_diff($modelsdateF, $modeldateF);
                    $diffF = $diffF->format("%d");
                    $diffF = round($diffF/2);

                    foreach($intento AS $item){

                        $modelH = new Histcrop();
                        $modelH->Hybrid_idHybrid = $item->hybridIdHybr->idHybrid;
                        if ($dias == 1) {
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU - " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale - ($diffF + $model->hybridIdHybr->sowingFemale);
                        } else {
                            $item->pollenColectU = date('Y-m-d', strtotime("$item->pollenColectU + " . ($diffF + $model->hybridIdHybr->pollenColectU) . " day"));
                            $modelH->days = $item->hybridIdHybr->cropIdcrops->sowingFemale + ($diffF + $model->hybridIdHybr->sowingFemale);
                        }

                        $modelH->proces = "Siembra de hembra.";
                        $item->save();
                        $modelH->save();
                    }
                }
            }
            if($model->realisedNrOfPlantsM && $model->extractedPlantsM){
                $model->remainingPlantsM = $model->realisedNrOfPlantsM-$model->extractedPlantsM;
            }

            if($model->realisedNrOfPlantsF && $model->extractedPlantsF){
                $model->remainingPlantsF = $model->realisedNrOfPlantsF-$model->extractedPlantsF;
            }
//            $model->hybridIdHybr->cropIdcrops->save()

            if (!($model->steamDesinfectionU >= $model->ReqDeliveryDate)) {
                $model->check = "Great, no problem.";
            } else {
                $model->check = "Check it";
            }

            // movilidad de filas de los crops

            if (($modelNHC = NumcropHasCompartment::findOne(['numcrop_cropnum' => $models->numCrop, 'compartment_idCompartment' => $models->compartment_idCompartment])) !== null) {
                $modelNHC->rowsLeft = $modelNHC->rowsLeft + $models->numRows;
                $modelNHC->rowsOccupied = $modelNHC->rowsOccupied - $models->numRows;
                $modelNHC->lastUpdatedDate = date("Y-m-d");
                $modelNHC->freeDate = $model->steamDesinfectionU;
                $modelNHC->save();
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
            if (($modelN = Numcrop::findOne(['cropnum' => $model->numCrop])) !== null) {
            }else{
                $modelE = new Numcrop();
                $modelE->cropnum = $model->numCrop;
                $modelE->save();
            }
            if (($modelNHC = NumcropHasCompartment::findOne(['numcrop_cropnum' => $model->numCrop, 'compartment_idCompartment' => $model->compartment_idCompartment])) !== null) {
                $modelNHC->rowsLeft = $modelNHC->rowsLeft - $models->numRows;
                $modelNHC->rowsOccupied = $modelNHC->rowsOccupied + $models->numRows;
                $modelNHC->lastUpdatedDate = date("Y-m-d");
                $modelNHC->freeDate = $model->steamDesinfectionU;
                $modelNHC->save();
            } else {
                $modelNew = new NumcropHasCompartment();
                $modelNew->rowsLeft = $model->compartmentIdCompartment->rowsNum - $model->numRows;
                $modelNew->rowsOccupied = $model->numRows;
                $modelNew->numcrop_cropnum = $model->numCrop;
                $modelNew->compartment_idCompartment = $model->compartment_idCompartment;
                $modelNew->createDate = date('Y-m-d');
                $modelNew->freeDate = $model->steamDesinfectionU;
                $modelNew->save();
            }

            if($models->compartment_idCompartment == $model->compartment_idCompartment){
                if($models->numRows !== $model->numRows) {
                    if (($modelNHC = NumcropHasCompartment::findOne(['numcrop_cropnum' => $model->numCrop, 'compartment_idCompartment' => $model->compartment_idCompartment])) !== null) {
                        $modelNHC->rowsLeft = $modelNHC->rowsLeft - $model->numRows + $models->numRows;
                        $modelNHC->rowsOccupied = $modelNHC->rowsOccupied + $model->numRows - $models->numRows;
                        $modelNHC->lastUpdatedDate = date("Y-m-d");
                        $modelNHC->freeDate = $model->steamDesinfectionU;
                        $modelNHC->save();
                    }
                }
            }


        }
    }
?>

