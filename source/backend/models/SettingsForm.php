<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Class SettingsForm
 *
 * @property string $currencies_request_url
 * @property string $rates_request_url
 * @property float $frequency_success_hrs
 * @property float $frequency_fail_hrs
 * @property float $lifetime_hrs
 * @property string $admin_email
 *
 * @package backend\models
 */

class SettingsForm extends Model
{
    public $currencies_request_url;
    public $rates_request_url;
    public $frequency_success_hrs;
    public $frequency_fail_hrs;
    public $lifetime_hrs;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'currencies_request_url', 'rates_request_url',
                    'frequency_success_hrs', 'frequency_fail_hrs', 'lifetime_hrs',
                ],
                'required'
            ],
            [['frequency_success_hrs', 'lifetime_hrs'], 'number', 'min' => 1],
            ['frequency_fail_hrs', 'number', 'min' => 0.16], // ~10 minutes
            [['currencies_request_url', 'rates_request_url'], 'url'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'currencies_request_url' => Yii::t('app', 'Request currencies URL'),
            'rates_request_url' => Yii::t('app', 'Request rates URL'),
            'frequency_success_hrs' => Yii::t('app', 'Update frequency (after success), hrs'),
            'frequency_fail_hrs' => Yii::t('app', 'Update frequency (after fail), hrs'),
            'lifetime_hrs' => Yii::t('app', 'Update lifetime, hrs')
        ];
    }
}
