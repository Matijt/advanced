<?php

namespace backend\controllers;

use backend\models\Hybrid;
use Yii;
use backend\models\NumcropHasCompartment;
use backend\models\Numcrop;
use backend\models\Order;
use backend\models\Mother;
use backend\models\OrderSearch;
use backend\models\OrderSearchm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use backend\codigo\Facil;
use yii\grid\GridView;
use yii\data\SqlDataProvider;
use kartik\export\ExportMenu;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }


    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'order.delete',0])
        ->andFilterWhere(['=', 'hybrid.delete',0])
            ->all();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionPlantm()
    {
        $searchModel = new OrderSearchm();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('plantM', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Order();

        if ($model->load(Yii::$app->request->post())) {
            $model->gpOrder = $_POST['gpOrder'];
            $gp = $model->gpOrder;
            $kg = $model->orderKg;
            if($gp && $kg) {
                // Equivalencia de Kilogramos a gramos.
                $g = $kg*1000;
                // Cantidad de plantas Hembras:
                $cph = ceil($g/$gp);
                // Cantidad de plantas Macho:
                $cpm = ceil($cph/5.25);
                // Cantidad de plantas totales:
                $cpt = $cph+$cpm;
                // Cantidad de líneas:
                $cl = ceil(($cpt/75));
                $model->numRows = $cl;
                // Inicializamos las variables para la cantidad final de plantas
                $cphT = 0;
                // Evaluamos si la cantidad de líneas es mayor a 4 y asigna un valor a la cantidad de plantas totales:
                if($cl > 4){
                    $cphT = ceil($cl*63);
                }else{
                    $cphT = ceil($cl*60);
                }
                // Sacamos la estimación con respecto a lo que vamos a plantar:
                $estimacionG = (ceil($cphT*$gp));
                $estimacionKg = $estimacionG/1000;

            }

            $model->plantingDistance = 50;
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
            $model->calculatedYield = ($model->netNumOfPlantsF*$model->hybridIdHybr->motherIdMother->gP)/1000;

            $connection = Yii::$app->getDb();
            $command = $connection->createCommand("SELECT MAX(numcrop_cropnum) AS actualCrop, rowsOccupied, rowsLeft
    FROM numcrop_has_compartment WHERE compartment_idCompartment = :compartment", [':compartment' => $model->compartment_idCompartment]);
            $query = $command->queryAll();
            $actualcrop = ArrayHelper::getValue($query, '0');
            $actualcrop = ArrayHelper::getValue($actualcrop, 'actualCrop');
            if(!isset($actualcrop)){
                $actualcrop = 1;
            }

            $hola = NumcropHasCompartment::find()->andFilterWhere(['=', 'compartment_idCompartment', $model->compartmentIdCompartment->compNum])
                ->andFilterWhere(['=', 'numcrop_cropnum', $actualcrop])
                ->andFilterWhere(['=' ,'crop_idcrops',1])
                ->all();

            foreach ($hola AS $crop){
                $actualcrop = $actualcrop -1;
            }
// Si el número del crop anterior no es 0, utiliza la fecha del crop actual, si el número de crop anterior es 0 utiliza el actual.

            $lastCrop = NumcropHasCompartment::find()->where('(numcrop_cropnum = :crop) AND compartment_idCompartment = :comp', ['crop' =>  ($actualcrop), 'comp' => $model->compartment_idCompartment])->all();
            $varExtra = 0;
            if(($actualcrop-1)===0) {
                foreach ($lastCrop AS $item) {
                    $model->sowingDateM = date('Y-m-d', strtotime("$item->freeDate - ".(($model->hybridIdHybr->cropIdcrops->transplantingMale)-1)." day"));
                }
                $varExtra = 1;
            }
            if(
                ($crop = NumcropHasCompartment::find()->where('(numcrop_cropnum = :crop) AND compartment_idCompartment = :comp', ['crop' =>  ($actualcrop)-1, 'comp' => $model->compartment_idCompartment])->all())
                &&
                ($varExtra == 0)
            ){
                foreach ($crop AS $item) {
                    $model->sowingDateM = date('Y-m-d', strtotime("$item->freeDate - ".(($model->hybridIdHybr->cropIdcrops->transplantingMale)-1)." day"));
                }
            }
            $model->ReqDeliveryDate = date('Y-m-d', strtotime($model->ReqDeliveryDate));
            $model->orderDate = date('Y-m-d', strtotime($model->orderDate));
            $model->ssRecDate = date('Y-m-d', strtotime($model->ssRecDate));
            $model->sowingDateM = date('Y-m-d', strtotime($model->sowingDateM));
            $mes = date('n', strtotime($model->sowingDateM));
            $dia = date('j', strtotime($model->sowingDateM));
            $model->compartmentIdCompartment->compNum;

            $F1 = $model->hybridIdHybr->cropIdcrops->sowingFemale;
            $TM = $model->hybridIdHybr->cropIdcrops->transplantingMale;
            $TF = $model->hybridIdHybr->cropIdcrops->transplantingFemale;
            $PF = $model->hybridIdHybr->cropIdcrops->pollinitionF;
            $PU = $model->hybridIdHybr->cropIdcrops->pollinitionU;
            $HF = $model->hybridIdHybr->cropIdcrops->harvestF;
            $HU = $model->hybridIdHybr->cropIdcrops->harvestU;
            $SDA = $model->hybridIdHybr->cropIdcrops->steamDesinfection;

            $model->sowingDateF = date('Y-m-d', strtotime("$model->sowingDateM + " . ($F1+$model->hybridIdHybr->sowingFemale) . " day"));
            $model->transplantingM = date('Y-m-d', strtotime("$model->sowingDateM + " . ($TM+$model->hybridIdHybr->transplantingMale) . " day"));
            $model->transplantingF = date('Y-m-d', strtotime("$model->sowingDateF + " . ($TF+$model->hybridIdHybr->transplantingFemale) . " day"));

            if (($mes <= 3)) {
                $model->transplantingM = date('Y-m-d', strtotime("$model->transplantingM + 7 day"));
                $model->transplantingF = date('Y-m-d', strtotime("$model->transplantingF + 7 day"));
                if ($mes == 3 && $dia > 10) {
                    $model->transplantingM = date('Y-m-d', strtotime("$model->transplantingM - 7 day"));
                    $model->transplantingF = date('Y-m-d', strtotime("$model->transplantingF - 7 day"));
                }
            } elseif (($mes == 12)) {
                if ($dia > 10) {
                    $model->transplantingM = date('Y-m-d', strtotime("$model->transplantingM + 7 day"));
                    $model->transplantingF = date('Y-m-d', strtotime("$model->transplantingF + 7 day"));
                }
            }

            $model->pollenColectF = date('Y-m-d', strtotime("$model->transplantingM + " . (14+$model->hybridIdHybr->pollenColectF) . " day"));
            $model->pollenColectU = date('Y-m-d', strtotime("$model->pollenColectF + " . (112+$model->hybridIdHybr->pollenColectU) . " day"));
            $model->pollinationF = date('Y-m-d', strtotime("$model->transplantingF + " . ($PF+$model->hybridIdHybr->pollinitionF) . " day"));
            $model->pollinationU = date('Y-m-d', strtotime("$model->pollinationF + " . ($PU+$model->hybridIdHybr->pollinitionU) . " day"));
            $model->harvestF = date('Y-m-d', strtotime("$model->pollinationF + " . ($HF+$model->hybridIdHybr->harvestF) . " day"));
            $model->harvestU = date('Y-m-d', strtotime("$model->harvestF + " . ($HU+$model->hybridIdHybr->harvestU) . " day"));
            $model->steamDesinfectionF = $model->harvestU;
            $model->steamDesinfectionU = date('Y-m-d', strtotime("$model->steamDesinfectionF + " . ($SDA+$model->hybridIdHybr->steamDesinfection) . " day"));

            if (!($model->steamDesinfectionU >= $model->ReqDeliveryDate)) {
                $model->check = "Great, no problem.";
            } else {
                $model->check = "Check it";
            }
            $connection = Yii::$app->getDb();
            $command = $connection->createCommand("SELECT MAX(numcrop_cropnum) AS actualCrop, rowsOccupied, rowsLeft
    FROM numcrop_has_compartment WHERE compartment_idCompartment = :compartment", [':compartment' => $model->compartment_idCompartment]);
            $query = $command->queryAll();
            $actualcrop = ArrayHelper::getValue($query, '0');
            $actualcrop = ArrayHelper::getValue($actualcrop, 'actualCrop');
            if(!isset($actualcrop)){
                $actualcrop = 1;
            }
            $model->numCrop = $actualcrop;

            $rowsAll = $connection->createCommand("SELECT MAX(numcrop_cropnum) AS actualCrop, rowsOccupied, rowsLeft
    FROM numcrop_has_compartment WHERE compartment_idCompartment = :compartment AND numcrop_cropnum = :numcomp", [':compartment' => $model->compartment_idCompartment, ':numcomp' => $actualcrop]);
            $queryR = $rowsAll->queryAll();
            $actualrows = ArrayHelper::getValue($queryR, '0');

            $rowsO = ArrayHelper::getValue($actualrows, 'rowsOccupied');
            $rowsL = ArrayHelper::getValue($actualrows, 'rowsLeft');

            $command = $connection->createCommand("SELECT MAX(numcrop_cropnum) AS maxCrop
            FROM numcrop_has_compartment");
            $maxCrop = $command->queryAll();
            $maxCrop = ArrayHelper::getValue($maxCrop, '0');
            $maxCrop = ArrayHelper::getValue($maxCrop, 'maxCrop');

            if(isset($maxCrop)){
                if(isset($actualcrop)){
                    if(0 == ($rowsL - $model->numRows)){
                        if(isset($rowsL)){
                            $actualcrop = $actualcrop +1;
                            if($actualcrop > $maxCrop){
                                $modelNum = new Numcrop();
                                $modelNum->save();
                            }
                            $model->numCrop = $actualcrop-1;
                        }
                        $modelNC = new NumcropHasCompartment();
                        $modelNC->createDate = date('Y-m-d');
                        $modelNC->rowsOccupied = 0;
                        $modelNC->rowsLeft = ($model->compartmentIdCompartment->rowsNum);
                        $modelNC->compartment_idCompartment = $model->compartment_idCompartment;
                        $modelNC->numcrop_cropnum = $actualcrop;
                        $modelNC->crop_idcrops = 1;

                        $modelNC->save();

                        $modelOld = NumcropHasCompartment::findOne(['numcrop_cropnum' => ($actualcrop-1), 'compartment_idCompartment' => $model->compartment_idCompartment]);
                        $modelOld->rowsLeft = new \stdClass();
                        $modelOld->rowsLeft = 0;
                        $modelOld->rowsOccupied = $model->compartmentIdCompartment->rowsNum;

                        $modelOld->save();
                        print_r($modelOld->getErrors());
                        print_r($modelNC->getErrors());

                    }else{
                        $has = NumcropHasCompartment::findOne(['numcrop_cropnum' => $actualcrop, 'compartment_idCompartment' => $model->compartment_idCompartment]);
                        if($has) {
                            $has->rowsOccupied = $has->rowsOccupied + $model->numRows;
                            $has->rowsLeft = $has->rowsLeft - $model->numRows;
                            if ($model->steamDesinfectionU > $has->freeDate) {
                                $has->freeDate = $model->steamDesinfectionU;
                            }
                            $has->save();
                        }
                    }
                }
            }else{
                $modelN = new Numcrop();
                $modelN->save();
            }

            if ($model->save()) {
                return $this->actionIndex();
            } else {
                return $this->renderAjax('create', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * See historial of the variaty.
     * @return mixed
     */
    public function actionHistorial()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'delete',0])
            ->all();

        return $this->render('history');
    }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {

        $model = $this->findModel($id);
        $models = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {

            if($model->numRows != $models->numRows){
                $model->gpOrder = $_POST['gpOrder'];
                $gp = $model->gpOrder;
                $kg = $model->orderKg;
                    // Equivalencia de Kilogramos a gramos.
                    $g = $kg*1000;
                    // Cantidad de plantas Hembras:
                    $cph = ceil($g/$gp);
                    // Cantidad de plantas Macho:
                    $cpm = ceil($cph/5.25);
                    // Cantidad de plantas totales:
                    $cpt = $cph+$cpm;
                    // Cantidad de líneas:
                    $cl = ceil(($cpt/75));
                    $model->numRows = $cl;
                    // Inicializamos las variables para la cantidad final de plantas
                    $cphT = 0;
                    // Evaluamos si la cantidad de líneas es mayor a 4 y asigna un valor a la cantidad de plantas totales:
                    if($cl > 4){
                        $cphT = ceil($cl*63);
                    }else{
                        $cphT = ceil($cl*60);
                    }
                    // Sacamos la estimación con respecto a lo que vamos a plantar:
                    $estimacionG = (ceil($cphT*$gp));
                    $estimacionKg = $estimacionG/1000;

            }
            $hecho = new Facil();
            $hecho->editar($model, $models);

            if ($model->save() && $model->hybridIdHybr->cropIdcrops->save()) {
            return $this->actionIndex();
            }else{
                return $this->renderAjax('update', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
      }

    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (($modelNHC = NumcropHasCompartment::findOne(['numcrop_cropnum' => ($model->numCrop), 'compartment_idCompartment' => $model->compartment_idCompartment])) !== null){
            if($modelNHC->rowsLeft == 0){
                $modelNHC->lastUpdatedDate = date("Y-m-d");
                $modelNHC->rowsOccupied = $modelNHC->rowsOccupied - $model->numRows;
                $modelNHC->rowsLeft = $modelNHC->rowsLeft + $model->numRows;
                $modelNHC->save();
            }else{
                $modelNHC->lastUpdatedDate = date("Y-m-d");
                $modelNHC->rowsOccupied = $modelNHC->rowsOccupied - $model->numRows;
                $modelNHC->rowsLeft = $modelNHC->rowsLeft + $model->numRows;
                $modelNHC->save();
            }
        } elseif (($modelNHC = NumcropHasCompartment::findOne(['numcrop_cropnum' => ($model->numCrop)-1, 'compartment_idCompartment' => $model->compartment_idCompartment])) !== null){
            if($modelNHC->rowsLeft == 0){
                $modelNHC->lastUpdatedDate = date("Y-m-d");
                $modelNHC->rowsOccupied = $modelNHC->rowsOccupied - $model->numRows;
                $modelNHC->rowsLeft = $modelNHC->rowsLeft + $model->numRows;
                $modelNHC->save();
            }else{
                $modelNHC->lastUpdatedDate = date("Y-m-d");
                $modelNHC->rowsOccupied = $modelNHC->rowsOccupied - $model->numRows;
                $modelNHC->rowsLeft = $modelNHC->rowsLeft + $model->numRows;
                $modelNHC->save();
            }
        }
        $model->delete = 1;
        $model->save();
        if(isset($_GET['name'])){
            $vista = $_GET['name'];
        }
        if(isset($vista)) {
            return $this->redirect([$vista.'index']);
        }else{
            return $this->redirect(['index']);
        }
    }

    public function actionOrderhistory($columns, $id ){
//            $columns = "c.compNum,h.variety AS Hybrid,m.variety AS Mother,f.variety AS Father,cr.crop,n.numcompartment AS Nursery,o.numCrop,o.orderKg,o.numRows";
//            $id = 1;
                $columnSorts = explode(",",$columns);
                $sorts = "";
                $validar = array();
                $validar[0] = '/c.compNum AS /';
                $validar[1] = '/c.rowsNum AS /';
                $validar[2] = '/h.variety AS /';
                $validar[3] = '/m.variety AS /';
                $validar[4] = '/m.steril AS /';
                $validar[5] = '/m.germination AS /';
                $validar[6] = '/m.tsw AS /';
                $validar[7] = '/f.variety AS /';
                $validar[8] = '/f.steril AS /';
                $validar[9] = '/f.germination AS /';
                $validar[10] = '/f.tsw AS /';
                $validar[11] = '/n.numcompartment AS /';
                $validar[12] = '/cr.crop AS /';

                $remplazar = array();
                $remplazar[2] = "";
                $remplazar[3] = "";
                $remplazar[4] = "";
                $remplazar[5] = "";
                $remplazar[6] = "";
                $remplazar[7] = "";
                $remplazar[8] = "";
                $remplazar[9] = "";
                $remplazar[10] = "";
                $remplazar[11] = "";
                $remplazar[12] = "";
                foreach($columnSorts AS $sort){
                    $sort = preg_replace($validar, $remplazar, $sort);
                    $sorts = $sorts.
                        "'".$sort."' => [
                            'asc' =>['".$sort."' => SORT_ASC],
                            'desc' =>['".$sort."' => SORT_DESC],
                            'default' =>['".$sort."' => SORT_DESC],
                            'label' => '".$sort."',
                        ], ";
                }
//                print_r($sorts);
                explode(",",$sorts);
                    $dataProvider = new SqlDataProvider([
                        'sql' => 'SELECT DISTINCT '.$columns.' 
                    FROM `order` o 
                    INNER JOIN `compartment` c
                    INNER JOIN `hybrid` h
                    INNER JOIN `Mother` m
                    INNER JOIN `Father` f
                    INNER JOIN `Crop` cr
                    INNER JOIN `Nursery` n
                    WHERE o.Hybrid_idHybrid = :id
                    AND c.idcompartment = o.compartment_idCompartment
                    AND h.idHybrid = o.Hybrid_idHybrid
                    AND h.Mother_idMother = m.idMother
                    AND h.Father_idFather = f.idFather
                    AND h.Crop_idCrops = cr.idcrops
                    AND n.idnursery = o.nursery_idnursery
                    AND o.delete = 0
                    ',
                        'params' => [
                            ':id' => $id
                        ],
                        //'sort' =>false, to remove the table header sorting
                        'sort' => [
                            'attributes' => [
                                'Compartment' => [ 'asc' =>['Compartment' => SORT_ASC], 'desc' =>['Compartment' => SORT_DESC], 'default' =>['Compartment' => SORT_DESC], 'label' => 'Compartment', ], '`Rows Num Comp`' => [ 'asc' =>['`Rows Num Comp`' => SORT_ASC], 'desc' =>['`Rows Num Comp`' => SORT_DESC], 'default' =>['`Rows Num Comp`' => SORT_DESC], 'label' => '`Rows Num Comp`', ], 'c.grossSurface' => [ 'asc' =>['c.grossSurface' => SORT_ASC], 'desc' =>['c.grossSurface' => SORT_DESC], 'default' =>['c.grossSurface' => SORT_DESC], 'label' => 'c.grossSurface', ], 'c.netSurface' => [ 'asc' =>['c.netSurface' => SORT_ASC], 'desc' =>['c.netSurface' => SORT_DESC], 'default' =>['c.netSurface' => SORT_DESC], 'label' => 'c.netSurface', ], 'c.grossLength' => [ 'asc' =>['c.grossLength' => SORT_ASC], 'desc' =>['c.grossLength' => SORT_DESC], 'default' =>['c.grossLength' => SORT_DESC], 'label' => 'c.grossLength', ], 'c.netLength' => [ 'asc' =>['c.netLength' => SORT_ASC], 'desc' =>['c.netLength' => SORT_DESC], 'default' =>['c.netLength' => SORT_DESC], 'label' => 'c.netLength', ], 'c.width' => [ 'asc' =>['c.width' => SORT_ASC], 'desc' =>['c.width' => SORT_DESC], 'default' =>['c.width' => SORT_DESC], 'label' => 'c.width', ], 'Hybrid' => [ 'asc' =>['Hybrid' => SORT_ASC], 'desc' =>['Hybrid' => SORT_DESC], 'default' =>['Hybrid' => SORT_DESC], 'label' => 'Hybrid', ], 'Mother' => [ 'asc' =>['Mother' => SORT_ASC], 'desc' =>['Mother' => SORT_DESC], 'default' =>['Mother' => SORT_DESC], 'label' => 'Mother', ], 'SterilMother' => [ 'asc' =>['SterilMother' => SORT_ASC], 'desc' =>['SterilMother' => SORT_DESC], 'default' =>['SterilMother' => SORT_DESC], 'label' => 'SterilMother', ], 'Germination_Mother' => [ 'asc' =>['Germination_Mother' => SORT_ASC], 'desc' =>['Germination_Mother' => SORT_DESC], 'default' =>['Germination_Mother' => SORT_DESC], 'label' => 'Germination_Mother', ], 'Thousand_seed_weight_Mother' => [ 'asc' =>['Thousand_seed_weight_Mother' => SORT_ASC], 'desc' =>['Thousand_seed_weight_Mother' => SORT_DESC], 'default' =>['Thousand_seed_weight_Mother' => SORT_DESC], 'label' => 'Thousand_seed_weight_Mother', ], 'm.gP' => [ 'asc' =>['m.gP' => SORT_ASC], 'desc' =>['m.gP' => SORT_DESC], 'default' =>['m.gP' => SORT_DESC], 'label' => 'm.gP', ], 'Father' => [ 'asc' =>['Father' => SORT_ASC], 'desc' =>['Father' => SORT_DESC], 'default' =>['Father' => SORT_DESC], 'label' => 'Father', ], 'Steril_Father' => [ 'asc' =>['Steril_Father' => SORT_ASC], 'desc' =>['Steril_Father' => SORT_DESC], 'default' =>['Steril_Father' => SORT_DESC], 'label' => 'Steril_Father', ], 'Germination_Father' => [ 'asc' =>['Germination_Father' => SORT_ASC], 'desc' =>['Germination_Father' => SORT_DESC], 'default' =>['Germination_Father' => SORT_DESC], 'label' => 'Germination_Father', ], 'f.pollenProduction' => [ 'asc' =>['f.pollenProduction' => SORT_ASC], 'desc' =>['f.pollenProduction' => SORT_DESC], 'default' =>['f.pollenProduction' => SORT_DESC], 'label' => 'f.pollenProduction', ], 'Thousand_seed_weight_Father' => [ 'asc' =>['Thousand_seed_weight_Father' => SORT_ASC], 'desc' =>['Thousand_seed_weight_Father' => SORT_DESC], 'default' =>['Thousand_seed_weight_Father' => SORT_DESC], 'label' => 'Thousand_seed_weight_Father', ], 'Crop' => [ 'asc' =>['Crop' => SORT_ASC], 'desc' =>['Crop' => SORT_DESC], 'default' =>['Crop' => SORT_DESC], 'label' => 'Crop', ], 'Nursery' => [ 'asc' =>['Nursery' => SORT_ASC], 'desc' =>['Nursery' => SORT_DESC], 'default' =>['Nursery' => SORT_DESC], 'label' => 'Nursery', ], 'n.tablesFloors' => [ 'asc' =>['n.tablesFloors' => SORT_ASC], 'desc' =>['n.tablesFloors' => SORT_DESC], 'default' =>['n.tablesFloors' => SORT_DESC], 'label' => 'n.tablesFloors', ], 'n.quantity' => [ 'asc' =>['n.quantity' => SORT_ASC], 'desc' =>['n.quantity' => SORT_DESC], 'default' =>['n.quantity' => SORT_DESC], 'label' => 'n.quantity', ], 'numCrop' => [ 'asc' =>['numCrop' => SORT_ASC], 'desc' =>['numCrop' => SORT_DESC], 'default' =>['numCrop' => SORT_DESC], 'label' => 'numCrop', ], 'orderKg' => [ 'asc' =>['orderKg' => SORT_ASC], 'desc' =>['orderKg' => SORT_DESC], 'default' =>['orderKg' => SORT_DESC], 'label' => 'orderKg', ], 'numRows' => [ 'asc' =>['numRows' => SORT_ASC], 'desc' =>['numRows' => SORT_DESC], 'default' =>['numRows' => SORT_DESC], 'label' => 'numRows', ], 'calculatedYield' => [ 'asc' =>['calculatedYield' => SORT_ASC], 'desc' =>['calculatedYield' => SORT_DESC], 'default' =>['calculatedYield' => SORT_DESC], 'label' => 'calculatedYield', ], 'netNumOfPlantsF' => [ 'asc' =>['netNumOfPlantsF' => SORT_ASC], 'desc' =>['netNumOfPlantsF' => SORT_DESC], 'default' =>['netNumOfPlantsF' => SORT_DESC], 'label' => 'netNumOfPlantsF', ], 'ReqDeliveryDate' => [ 'asc' =>['ReqDeliveryDate' => SORT_ASC], 'desc' =>['ReqDeliveryDate' => SORT_DESC], 'default' =>['ReqDeliveryDate' => SORT_DESC], 'label' => 'ReqDeliveryDate', ], 'contractNumber' => [ 'asc' =>['contractNumber' => SORT_ASC], 'desc' =>['contractNumber' => SORT_DESC], 'default' =>['contractNumber' => SORT_DESC], 'label' => 'contractNumber', ], 'ssRecDate' => [ 'asc' =>['ssRecDate' => SORT_ASC], 'desc' =>['ssRecDate' => SORT_DESC], 'default' =>['ssRecDate' => SORT_DESC], 'label' => 'ssRecDate', ], 'sowingM' => [ 'asc' =>['sowingM' => SORT_ASC], 'desc' =>['sowingM' => SORT_DESC], 'default' =>['sowingM' => SORT_DESC], 'label' => 'sowingM', ], 'sowingF' => [ 'asc' =>['sowingF' => SORT_ASC], 'desc' =>['sowingF' => SORT_DESC], 'default' =>['sowingF' => SORT_DESC], 'label' => 'sowingF', ], 'nurseryM' => [ 'asc' =>['nurseryM' => SORT_ASC], 'desc' =>['nurseryM' => SORT_DESC], 'default' =>['nurseryM' => SORT_DESC], 'label' => 'nurseryM', ], 'nurseryF' => [ 'asc' =>['nurseryF' => SORT_ASC], 'desc' =>['nurseryF' => SORT_DESC], 'default' =>['nurseryF' => SORT_DESC], 'label' => 'nurseryF', ], 'realisedNrOfPlantsM' => [ 'asc' =>['realisedNrOfPlantsM' => SORT_ASC], 'desc' =>['realisedNrOfPlantsM' => SORT_DESC], 'default' =>['realisedNrOfPlantsM' => SORT_DESC], 'label' => 'realisedNrOfPlantsM', ], 'realisedNrOfPlantsF' => [ 'asc' =>['realisedNrOfPlantsF' => SORT_ASC], 'desc' =>['realisedNrOfPlantsF' => SORT_DESC], 'default' =>['realisedNrOfPlantsF' => SORT_DESC], 'label' => 'realisedNrOfPlantsF', ], 'remainingPlantsM' => [ 'asc' =>['remainingPlantsM' => SORT_ASC], 'desc' =>['remainingPlantsM' => SORT_DESC], 'default' =>['remainingPlantsM' => SORT_DESC], 'label' => 'remainingPlantsM', ], 'remainingPlantsF' => [ 'asc' =>['remainingPlantsF' => SORT_ASC], 'desc' =>['remainingPlantsF' => SORT_DESC], 'default' =>['remainingPlantsF' => SORT_DESC], 'label' => 'remainingPlantsF', ], 'sowingDateM' => [ 'asc' =>['sowingDateM' => SORT_ASC], 'desc' =>['sowingDateM' => SORT_DESC], 'default' =>['sowingDateM' => SORT_DESC], 'label' => 'sowingDateM', ], 'sowingDateF' => [ 'asc' =>['sowingDateF' => SORT_ASC], 'desc' =>['sowingDateF' => SORT_DESC], 'default' =>['sowingDateF' => SORT_DESC], 'label' => 'sowingDateF', ], 'transplantingM' => [ 'asc' =>['transplantingM' => SORT_ASC], 'desc' =>['transplantingM' => SORT_DESC], 'default' =>['transplantingM' => SORT_DESC], 'label' => 'transplantingM', ], 'transplantingF' => [ 'asc' =>['transplantingF' => SORT_ASC], 'desc' =>['transplantingF' => SORT_DESC], 'default' =>['transplantingF' => SORT_DESC], 'label' => 'transplantingF', ], 'pollenColectF' => [ 'asc' =>['pollenColectF' => SORT_ASC], 'desc' =>['pollenColectF' => SORT_DESC], 'default' =>['pollenColectF' => SORT_DESC], 'label' => 'pollenColectF', ], 'pollenColectU' => [ 'asc' =>['pollenColectU' => SORT_ASC], 'desc' =>['pollenColectU' => SORT_DESC], 'default' =>['pollenColectU' => SORT_DESC], 'label' => 'pollenColectU', ], 'pollenColectQ' => [ 'asc' =>['pollenColectQ' => SORT_ASC], 'desc' =>['pollenColectQ' => SORT_DESC], 'default' =>['pollenColectQ' => SORT_DESC], 'label' => 'pollenColectQ', ], 'pollinationF' => [ 'asc' =>['pollinationF' => SORT_ASC], 'desc' =>['pollinationF' => SORT_DESC], 'default' =>['pollinationF' => SORT_DESC], 'label' => 'pollinationF', ], 'pollinationU' => [ 'asc' =>['pollinationU' => SORT_ASC], 'desc' =>['pollinationU' => SORT_DESC], 'default' =>['pollinationU' => SORT_DESC], 'label' => 'pollinationU', ], 'harvestF' => [ 'asc' =>['harvestF' => SORT_ASC], 'desc' =>['harvestF' => SORT_DESC], 'default' =>['harvestF' => SORT_DESC], 'label' => 'harvestF', ], 'harvestU' => [ 'asc' =>['harvestU' => SORT_ASC], 'desc' =>['harvestU' => SORT_DESC], 'default' =>['harvestU' => SORT_DESC], 'label' => 'harvestU', ], 'steamDesinfectionF' => [ 'asc' =>['steamDesinfectionF' => SORT_ASC], 'desc' =>['steamDesinfectionF' => SORT_DESC], 'default' =>['steamDesinfectionF' => SORT_DESC], 'label' => 'steamDesinfectionF', ], 'steamDesinfectionU' => [ 'asc' =>['steamDesinfectionU' => SORT_ASC], 'desc' =>['steamDesinfectionU' => SORT_DESC], 'default' =>['steamDesinfectionU' => SORT_DESC], 'label' => 'steamDesinfectionU', ], 'remarks' => [ 'asc' =>['remarks' => SORT_ASC], 'desc' =>['remarks' => SORT_DESC], 'default' =>['remarks' => SORT_DESC], 'label' => 'remarks', ],
                            ],
                        ],

                        'pagination' => [
                            'pageSize' => 20,
                        ],
                    ]);

                     $searchModel = new OrderSearch();
                    // I tried with this one to but it didin´t work :(
                    // $searchModel = $dataProvider->getModels();

            // get the user records in the current page
                    $model = $dataProvider->getModels();
/*
                    echo ExportMenu::widget([
                        'dataProvider' => $dataProvider,
                    ]);
*/
                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
//                        'columns' => ['class' => 'yii\grid\ActionColumn'],
                    ]);
//                    echo "<a href='index.php?r=order/pdforder'>halo</a>";
    }

    public function actionHistory($id)
    {
        if($id) {
//            echo ' <input type="text" id="Hola" name="myText2" value="" selectBoxOptions="';
            $countOrders = Order::find()
                ->where(['Hybrid_idHybrid' => $id])
                ->count();
            $hybrid = Hybrid::findOne(['idHybrid' => $id]);
            if ($countOrders > 0) {
                $data = Order::findBySql('SELECT o.sowingDateM, AVG(o.gpOrder) AS gpOrder FROM `order` o WHERE (Hybrid_idHybrid = ' . $id . ' AND o.delete = 0) AND (o.selector = "Active" AND o.state = "Order finish")
                                            UNION 
                                            SELECT "Holland", gP as HollandGP FROM mother WHERE idMother = ' . $hybrid->motherIdMother->idMother . ';
                   ')->all();
                if ($data) {
                    foreach ($data AS $dat) {
                        if ($dat->sowingDateM == "Holland") {
                            echo "From " . $dat->sowingDateM . ", g/p: " . $dat->gpOrder;
                        } else {
                            if(isset($dat->gpOrder )) {
                                echo "From PROMEX, gp: " . $dat->gpOrder . ";";
                            }
                        }
                    }
                }
            } else {
                $data = Mother::findOne(["idMother" => $hybrid->motherIdMother->idMother]);
                if ($data) {
                    echo "From Holland, g/p: " . $data->gP;
                }
            }
        }
    }
    public function actionOrder()

    {

        $model = new \backend\models\Order();



        if ($model->load(Yii::$app->request->post())) {

            if ($model->validate()) {

                // form inputs are valid, do something here

                return;

            }

        }



        return $this->render('/order', [

            'model' => $model,

        ]);

    }



    public function actionCompartment($gp, $kg, $idc=0, $numc=0)
    {
        // Evalua si los datos fueron pasados exitosamente
        if($gp && $kg) {
            // Equivalencia de Kilogramos a gramos.
            $g = $kg*1000;
            // Cantidad de plantas Hembras:
            $cph = ceil($g/$gp);
            // Cantidad de plantas Macho:
            $cpm = ceil($cph/5.25);
            // Cantidad de plantas totales:
            $cpt = $cph+$cpm;
            // Cantidad de líneas:
            $cl = ceil(($cpt/75));
            // Inicializamos las variables para la cantidad final de plantas
            $cphT = 0;
            // Evaluamos si la cantidad de líneas es mayor a 4 y asigna un valor a la cantidad de plantas totales:
            if($cl > 4){
                $cphT = ceil($cl*63);
            }else{
                $cphT = ceil($cl*60);
            }
            // Sacamos la estimación con respecto a lo que vamos a plantar:
            $estimacionG = (ceil($cphT*$gp));
            $estimacionKg = $estimacionG/1000;
            $compartments = NumcropHasCompartment::find()->joinWith(['compartmentIdCompartment'])->where('((numcrop_has_compartment.compartment_idCompartment = compartment.idCompartment) AND (numcrop_has_compartment.rowsLeft >= :row)) AND numcrop_has_compartment.freeDate IS NOT NULL', ['row' =>  $cl])->orderBy('numcrop_has_compartment.freeDate')->all();
            echo "<option value='0' disabled='true'>Rows you need: ".$cl.", Estimated production: ".$estimacionKg."</option>";
            // Validamos si es creación de orden o actualización dependiendo de los datos enviados:
            if($idc != 0 && $numc != 0){
                // Mostramos el compartimento actual:
                echo "<option value='".$idc."' selected='true'>Actual compartment: ".$numc."</option>";
                // Quitamos al compartimento ya mencionado previamente.
                $compartments = NumcropHasCompartment::find()->joinWith(['compartmentIdCompartment'])->where('((numcrop_has_compartment.compartment_idCompartment = compartment.idCompartment) AND (numcrop_has_compartment.rowsLeft >= :row)) AND compartment.idCompartment != :idc', ['row' =>  $cl,'idc' => $idc])->orderBy('compartment.compNum')->all();;
            }
            foreach ($compartments AS $compartment){
                echo "<option value='".$compartment->compartment_idCompartment."'>Compartment: ".$compartment->compartmentIdCompartment->compNum."; Crop: ".$compartment->numcrop_cropnum.", FreeDate: ".$compartment->freeDate.", Rows left: ".$compartment->rowsLeft.".";

            }
        }
    }

    /**
    * Allows to set able or disable an order for the evaluation.
     */
    public function actionChange($id){
        $order = Order::findOne(["idorder" => $id]);
        if($order->selector == "Active"){
            $order->selector = "Inactive";
            echo "selector: ".
                $order->selector;
            $order->save();
        }else{
            $order->selector = "Active";
            echo "selector: ".
                $order->selector;
            $order->save();
        }
    }

    /**
     * PDF export for Rijk Zwaan RPOMEX
     * good luck man.
     */
    public function actionPdf(){

        $excel = \Yii::createObject([
            'class' => 'codemix\excelexport\ExcelFile',
            'sheets' => [
                'Information' => [
                    'class' => 'codemix\excelexport\ActiveExcelSheet',
                    'query' => Order::find(),
                    'callbacks' => [
                        // $cell is a PHPExcel_Cell object
                        'A' => function ($cell) {
                            $cell->getStyle()->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                ],
                                'borders' => [
                                    'top' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                    ],
                                    'bottom' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                    ],
                                    'left' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                    ],
                                    'right' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                    ],
                                ],
                            ]);
                        },
                        ]
                ]
            ]
        ]);
        $excel->getWorkbook()->setActiveSheetIndex(0);
//        $excel->getActiveSheet()->getStyle('B6')->getFill()->getStartColor()->setARGB('FFFF0000');

        $excel->getWorkbook()
            ->getSheet()
            ->getStyle('B1')
            ->getFont()
            ->getColor()
            ->setARGB(\PHPExcel_Style_Color::COLOR_RED);
        $excel->getWorkbook()
            ->getSheet()
            ->getStyle('c3')
            ->getBorders()->getLeft()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THICK);
        $excel->getWorkbook()
            ->getSheet()
            ->getStyle('B2:B5')
            ->getFont()
            ->getColor()
            ->setARGB(\PHPExcel_Style_Color::COLOR_DARKGREEN);
        $excel->getWorkbook()->getSheet()->getStyle('B2:B6')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $excel->getWorkbook()
            ->getSheet()
            ->getStyle('B2:B6')
            ->getFill()
            ->getStartcolor()
            ->setArgb('FFFF00');
        $excel->getWorkbook()
            ->getSheet()
            ->getStyle('B2:B6')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_MEDIUM);
        $excel->getWorkbook()->getSheet()->getStyle('C2')
            ->getBorders()->getRight()->setBorderStyle('medium');
//        $excel->getPropierties()
//            ->setCreator("Matías Joaquín Tucci");
//            ->setLastModifyBy("Matías Joaquín Tucci")
//            ->setTitle("Rijk Zwaan PROMEX")
//            ->setSubject("Rijk Zwaan PROMEX 2017")
//            ->setDescription("Test")
//            ->setKeywords("extra text office excel 2007")
//            ->setCategory("Test result file");
        $excel->send('orders '.date("Y/m/d").'.xlsx');
        return $this->goHome();
    }

    /**
     * PDF export for orders
     * good luck man.
     */
    public function actionPdforder(){

        $excel = \Yii::createObject([
            'class' => 'codemix\excelexport\ExcelFile',
            'sheets' => [
                'Information' => [
                    'class' => 'codemix\excelexport\ActiveExcelSheet',
                    'query' => Order::find()->innerJoin('compartment'),
                ]
            ]
        ]);
        $excel->send('orders 2 '.date("Y/m/d").'.xlsx');

        return $this->render('/order%2Fhistorial', [
        ]);
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
