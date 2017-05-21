<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Currency Rate model
 *
 * @property integer $id
 * @property integer $currency_id
 * @property string $update_id
 * @property float $cb_value
 * @property float $value
 *
 * @property Currency $currency
 * @property RatesUpdate $update
 */
class CurrencyRate extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{rates}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
		    [['currency_id', 'update_id', 'cb_value', 'value'], 'required'],
            // TODO: $numberPattern для ограничения precision 4 знаками
			[['cb_value', 'value'], 'number', 'min' => 0],
			[['currency_id'], 'unique', 'targetAttribute' => ['currency_id', 'update_id']]
		];
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'currency_id' => Yii::t('app', 'Currency'), // 'Валюта',
            'update_id' => Yii::t('app', 'Rates Update'), // 'Обновление',
            'cb_value' => Yii::t('app', 'Bank of Russia rate, RUB'), // 'Курс ЦБ РФ, руб.',
            'value' => Yii::t('app', 'Corrected rate, RUB'), // 'Скорректированный курс, руб.'
        ];
    }

    /**
     * Relation with Currency entity
     * @return ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * Relation with RatesUpdate entity
     * @return ActiveQuery
     */
    public function getUpdate()
    {
        return $this->hasOne(RatesUpdate::className(), ['id' => 'update_id']);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->loadDefaultValues();
    }
}
