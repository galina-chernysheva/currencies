<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Currency model
 *
 * @property integer $id
 * @property string $cb_id
 * @property integer $iso_num_code
 * @property string $iso_char_code
 * @property string $name
 * @property string $en_name
 * @property integer $rate_divergence_pct
 * @property integer $nominal
 *
 * @property CurrencyRate[] $rates
 */
class Currency extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{currencies}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
            // 'iso_num_code', 'iso_char_code' тоже должны быть required,
            // но в списке валют ЦБ РФ существуют экзепляры с пустыми значениями
			[['cb_id', 'nominal', 'rate_divergence_pct'], 'required'],
			['cb_id', 'string', 'max' => 10],
			[['iso_num_code', 'nominal'], 'integer', 'min' => 1],
			['iso_char_code', 'string', 'min' => 3, 'max' => 3],
            ['iso_char_code', 'default', 'value' => null],
			[['name', 'en_name'], 'string', 'max' => 50],
			// теоретически может быть [-100..100], но на практике имеет мало смысла
			['rate_divergence_pct', 'integer', 'min' => 0, 'max' => 50],
            ['rate_divergence_pct', 'default', 'value' => 0],
			['cb_id', 'unique'],
            ['iso_num_code', 'unique'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'cb_id' => Yii::t('app', 'Bank internal ID'), // 'ID валюты ЦБ РФ',
			'iso_num_code' => Yii::t('app', 'ISO numeric code'), // 'ISO цифровой код',
			'iso_char_code' => Yii::t('app', 'ISO character code'), // 'ISO буквенный код',
			'nominal' => Yii::t('app', 'Nominal'), // 'Номинал',
			'name' => Yii::t('app', 'Name (RU)'), // 'Название',
			'en_name' => Yii::t('app', 'Name'), // 'Название (EN)',
			'rate_divergence_pct' => Yii::t('app', 'Rate divergence, pct'), // 'Корректировка курса, %'
		];
	}

    /**
     * Relation with Rates entities
     * @return ActiveQuery
     */
    public function getRates()
    {
        return $this->hasMany(CurrencyRate::className(), ['currency_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->loadDefaultValues();
    }

    /**
     * Finds currency by internal ID of bank currency entity
     *
     * @param string $cb_id
     * @return static|null
     */
    public static function findByCbId($cb_id)
    {
        return static::findOne(['cb_id' => $cb_id]);
    }
}
