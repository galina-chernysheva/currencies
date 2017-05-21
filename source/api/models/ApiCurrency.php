<?php

namespace api\models;

use common\models\Currency;

/**
 * Currency model heir for API
 */
class ApiCurrency extends Currency
{
    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['cb_id'], $fields['rate_divergence_pct']);
        return $fields;
    }

    /**
     * @inheritdoc
     * Related objects should be excluded from response
     */
    public function extraFields()
    {
        return [];
    }
}
