<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Currency;

/**
 * CurrencySearch represents the model behind the search form about `common\models\Currency`.
 */
class CurrencySearch extends Currency
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'iso_num_code', 'rate_divergence_pct', 'nominal'], 'integer'],
            [['cb_id', 'iso_char_code', 'name', 'en_name'], 'safe'],
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
        $query = Currency::find();

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'iso_num_code' => $this->iso_num_code
        ]);

        $query->andFilterWhere(['like', 'cb_id', $this->cb_id])
            ->andFilterWhere(['like', 'iso_char_code', $this->iso_char_code])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'en_name', $this->en_name]);

        return $dataProvider;
    }
}
