<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "historialcomp".
 *
 * @property int $idHistorialcomp
 * @property string $title
 * @property string $content
 * @property string $date
 * @property int $compartment_idCompartment
 *
 * @property Compartment $compartmentIdCompartment
 */
class Historialcomp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'historialcomp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'compartment_idCompartment'], 'required'],
            [['content'], 'string'],
            [['date'], 'safe'],
            [['compartment_idCompartment'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['compartment_idCompartment'], 'exist', 'skipOnError' => true, 'targetClass' => Compartment::className(), 'targetAttribute' => ['compartment_idCompartment' => 'idCompartment']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idHistorialcomp' => Yii::t('app', 'Id Historialcomp'),
            'title' => Yii::t('app', 'Title'),
            'content' => Yii::t('app', 'Content'),
            'date' => Yii::t('app', 'Date'),
            'compartment_idCompartment' => Yii::t('app', 'Compartment'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompartmentIdCompartment()
    {
        return $this->hasOne(Compartment::className(), ['idCompartment' => 'compartment_idCompartment']);
    }
}
