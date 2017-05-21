<?php
namespace common\widgets;

use Yii;
use common\models\RatesUpdate;

/**
 * Widget renders message about rates relevance in database with load button
 */
class RatesRelevanceInfo extends \yii\bootstrap\Widget
{
    /**
     * @var array the alert types configuration for the relevance info messages.
     * This array is setup as $key => $value, where:
     * - $key is the name of the const for Rates Update relevance state
     * - $value is the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        RatesUpdate::STATE_ERROR   => 'alert-danger',
        RatesUpdate::STATE_ACTUAL    => 'alert-info',
        RatesUpdate::STATE_EXPIRED => 'alert-warning'
    ];

    public function init()
    {
        parent::init();

        $type = RatesUpdate::STATE_EXPIRED;
        $message = Yii::t('app', 'No currencies rates data. Please, update rates.');
        $latestUpdate = RatesUpdate::getLatest();

        if ($latestUpdate) {
            if (!$latestUpdate->success) {
                $type = RatesUpdate::STATE_ERROR;
                $message = Yii::t(
                    'app', 'Latest update ({requested_at}) was failed. Please, update rates',
                    ['requested_at' => $latestUpdate->requested_at]
                );
            } elseif ($latestUpdate->isExpired()) {
                $type = RatesUpdate::STATE_EXPIRED;
                $message = Yii::t('app', 'Rates are expired. Please, update rates');
            } else {
                $type = RatesUpdate::STATE_ACTUAL;
                $message = Yii::t(
                    'app', 'Rates are actual (loaded {requested_at})',
                    ['requested_at' => $latestUpdate->requested_at]
                );
            }
        }

        $appendCss = isset($this->options['class']) ? ' ' . $this->options['class'] : '';
        $this->options['class'] = $this->alertTypes[$type] . $appendCss;





        echo \yii\bootstrap\Alert::widget([
            'body' => $message,
            'closeButton' => false,
            'options' => $this->options,
        ]);
    }
}
