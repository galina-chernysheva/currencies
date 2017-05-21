<?php
namespace common\models;

use Faker\Provider\cs_CZ\DateTime;
use Yii;
use yii\db\ActiveRecord;

/**
 * Currency Rates Update model
 *
 * @property integer $id
 * @property \DateTime $requested_at
 * @property \DateTime $rate_date
 * @property boolean $success
 *
 * @property integer $expireTime
 *
 * @property CurrencyRate[] $rates
 */
class RatesUpdate extends ActiveRecord
{
    const STATE_ERROR = 0;
    const STATE_ACTUAL = 1;
    const STATE_EXPIRED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{rates_updates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['requested_at', 'required'],
            ['requested_at', 'datetime', 'format' => 'php:Y-m-d H:i:s'],
//            ['requested_at', 'default', 'value' => date('Y-m-d H:i:s')],
            ['rate_date', 'date', 'format' => 'php:d.m.Y'],
            ['success', 'boolean', 'falseValue' => false, 'trueValue' => true],
            ['success', 'default', 'value' => false]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'requested_at' => Yii::t('app', 'Requested at'), // 'Дата обновления',
            'rate_date' => Yii::t('app', 'Rate date'), // 'Дата курса',
            'success' => Yii::t('app', 'Applied successfully'), //'Выполнено успешно'
        ];
    }

    /**
     * Relation with Currencies Rates entities
     * @return ActiveQuery
     */
    public function getRates()
    {
        return $this->hasMany(CurrencyRate::className(), ['update_id' => 'id']);
    }

    /**
     * Return UNIX timestamp of the update expire time
     * @return int
     */
    public function getExpireTime()
    {
        $lifetimeHrs = Yii::$app->settings->get('SettingsForm', 'lifetime_hrs');
        $requestTime = strtotime($this->requested_at);
        return $requestTime + round($lifetimeHrs * 3600);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->loadDefaultValues();
        $this->requested_at = date('Y-m-d H:i:s');
    }

    /**
     * Returns latest from RatesUpdate instances excluding broken (success=true, but no related rates)
     * @return array|null|ActiveRecord
     */
    public static function getLatest()
    {
        return static::find()
            ->alias('ru')
            ->joinWith('rates r', false)
            ->groupBy('ru.id')
            ->having(['ru.success' => false])
            ->orHaving('COUNT(r.id) > 0 AND ru.success=:success', [':success' => true])
            ->orderBy(['ru.requested_at' => SORT_DESC])
            ->one();
    }

    /**
     * Returns latest successful RatesUpdate instance excluding broken (no related rates)
     * @return array|null|ActiveRecord
     */
    public static function getLatestSuccessful()
    {
        return static::find()
            ->alias('ru')
            ->joinWith('rates r', false, 'INNER JOIN')
            ->groupBy('ru.id')
            ->having('COUNT(r.id) > 0 AND ru.success=:success', [':success' => true])
            ->orderBy(['ru.requested_at' => SORT_DESC])
            ->one();
    }

    /**
     * Returns RatesUpdate with rate info for currency ID equal $currencyId,
     * date equal $date or latest date if $date missed
     * @param $currencyId
     * @param null|string|DateTime $date
     */
    public static function getByCurrency($currencyId, $date=null)
    {
        $query = static::find()
            ->alias('ru')
            ->joinWith('rates r')
            ->where(['r.currency_id' => $currencyId])
            ->orderBy(['ru.rate_date' => SORT_DESC]);
        if ($date) {
            $query->andWhere(['ru.rate_date' => $date]);
        }
        return $query->one();
    }

    /**
     * Returns RatesUpdate with rate info for currency ID and min date difference to $date
     * @param $currencyId
     * @param string|DateTime $date
     */
    public static function getNearestByCurrency($currencyId, $date)
    {
        return static::find()
            ->select(['ru.*', new \yii\db\Expression("ABS(ru.rate_date - CAST('$date' AS date)) datediff")])
            ->alias('ru')
            ->joinWith('rates r')
            ->where(['r.currency_id' => $currencyId])
            ->andWhere('ru.rate_date <> :rate_date', [':rate_date' => $date])
            ->orderBy(['datediff' => SORT_ASC])
            ->one();
    }

    /**
     * Checks if RatesUpdate instance is expired
     * @return bool
     */
    public function isExpired()
    {
        return $this->expireTime <= time();
    }
}
