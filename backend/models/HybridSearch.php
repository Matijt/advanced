<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Hybrid;

/**
 * HybridSearch represents the model behind the search form of `backend\models\Hybrid`.
 */
class HybridSearch extends Hybrid
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idHybrid', 'sowingFemale', 'transplantingMale', 'transplantingFemale', 'pollenColectF', 'pollenColectU', 'pollinitionF', 'pollinitionU', 'harvestF', 'harvestU', 'steamDesinfection'], 'integer'],
            [['variety', 'remarks', 'Crop_idcrops', 'Father_idFather', 'Mother_idMother'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Hybrid::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // JOin With
        $query->joinWith('motherIdMother');
        $query->joinWith('fatherIdFather');
        $query->joinWith('cropIdcrops');

        // grid filtering conditions
        $query->andFilterWhere([
            'idHybrid' => $this->idHybrid,
            'sowingFemale' => $this->sowingFemale,
            'transplantingMale' => $this->transplantingMale,
            'transplantingFemale' => $this->transplantingFemale,
            'pollenColectF' => $this->pollenColectF,
            'pollenColectU' => $this->pollenColectU,
            'pollinitionF' => $this->pollinitionF,
            'pollinitionU' => $this->pollinitionU,
            'harvestF' => $this->harvestF,
            'harvestU' => $this->harvestU,
            'steamDesinfection' => $this->steamDesinfection,
        ]);

        $query
        ->andFilterWhere(['like', 'mother.variety', $this->Mother_idMother])
        ->andFilterWhere(['like', 'hybrid.variety', $this->variety])
        ->andFilterWhere(['like', 'father.variety', $this->Father_idFather])
        ->andFilterWhere(['like', 'crop.crop', $this->Crop_idcrops])
            ->andFilterWhere(['like', 'remarks', $this->remarks]);

        return $dataProvider;
    }
}
