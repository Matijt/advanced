<?php

namespace backend\controllers;

use Yii;
use backend\models\NumcropHasCompartment;
use backend\models\NumcropHasCompartmentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * NumcropHasCompartmentController implements the CRUD actions for NumcropHasCompartment model.
 */
class NumcropHasCompartmentController extends Controller
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
     * Lists all NumcropHasCompartment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NumcropHasCompartmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->addOrderBy('numcrop_cropnum DESC, crop_idcrops ASC');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single NumcropHasCompartment model.
     * @param integer $numcrop_cropnum
     * @param integer $compartment_idCompartment
     * @return mixed
     */
    public function actionView($numcrop_cropnum, $compartment_idCompartment)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($numcrop_cropnum, $compartment_idCompartment),
        ]);
    }

    /**
     * Creates a new NumcropHasCompartment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new NumcropHasCompartment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'numcrop_cropnum' => $model->numcrop_cropnum, 'compartment_idCompartment' => $model->compartment_idCompartment]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing NumcropHasCompartment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $numcrop_cropnum
     * @param integer $compartment_idCompartment
     * @return mixed
     */
    public function actionUpdate($numcrop_cropnum, $compartment_idCompartment)
    {
        $model = $this->findModel($numcrop_cropnum, $compartment_idCompartment);

        if ($model->load(Yii::$app->request->post()) ) {
            $modelOld = NumcropHasCompartment::findOne(['numcrop_cropnum' => (($model->numcrop_cropnum)-1), 'compartment_idCompartment' => $model->compartment_idCompartment]);
            $model->freeDate = date('Y-m-d', strtotime("$modelOld->freeDate  + ". ($model->cropIdcrops->durationOfTheCrop) . " day"));
            $model->lastUpdatedDate = date('Y-m-d');

            if($model->cropIdcrops->crop == "Not planned"){
                $model->freeDate = null;
            }
            if ($model->save()) {

                $searchModel = new NumcropHasCompartmentSearch();
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                $dataProvider->query->addOrderBy('numcrop_cropnum DESC, crop_idcrops ASC');

                return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);
            }else{
                return $this->render('update', [
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
     * Deletes an existing NumcropHasCompartment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $numcrop_cropnum
     * @param integer $compartment_idCompartment
     * @return mixed
     */
    public function actionDelete($numcrop_cropnum, $compartment_idCompartment)
    {
        $this->findModel($numcrop_cropnum, $compartment_idCompartment)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the NumcropHasCompartment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $numcrop_cropnum
     * @param integer $compartment_idCompartment
     * @return NumcropHasCompartment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($numcrop_cropnum, $compartment_idCompartment)
    {
        if (($model = NumcropHasCompartment::findOne(['numcrop_cropnum' => $numcrop_cropnum, 'compartment_idCompartment' => $compartment_idCompartment])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
