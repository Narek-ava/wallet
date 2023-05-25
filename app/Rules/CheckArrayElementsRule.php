<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckArrayElementsRule implements Rule
{

    protected array $requestArray;
    protected array $defaultArray;
    protected string $prefix;
    protected string $postfix;

    const  ERROR_MESSAGES = [
        'max_amount' => 'max_amount_wallester_limit',
        'transaction' => 'transaction_amount_wallester_limit',
        'daily' => 'daily_amount_wallester_limit',
        'weekly' => 'weekly_amount_wallester_limit',
    ];

    protected array $errorMessageParams = [];

    protected ?string $currentError;


    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $requestArray, array $defaultArray, string $prefix = '', string $postfix = '')
    {
        $this->defaultArray = $defaultArray;
        $this->prefix = $prefix;
        $this->postfix = $postfix;
        $this->requestArray = $requestArray;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $attribute = str_replace('limits.', '', $attribute);
        $checkKey = $this->prefix . $attribute . $this->postfix;

        if ($this->defaultArray[$checkKey] < $value) {
            $this->currentError = 'max_amount';
            $this->errorMessageParams['maxAmount'] = $this->defaultArray[$checkKey];
            return false;
        }

        $arr = explode('_', $attribute);
        $prefix = array_shift($arr);
        $attrNameWithoutPrefix = implode('_', $arr);

        $this->currentError = $prefix;

        switch ($prefix) {
            case 'transaction':
                $dailyAttr = $this->requestArray['daily_' . $attrNameWithoutPrefix];
                return $value <= $dailyAttr;
            case 'daily':
                $weeklyAttr = $this->requestArray['weekly_' . $attrNameWithoutPrefix];
                $monthlyAttr = $this->requestArray['monthly_' . $attrNameWithoutPrefix];
                return $value <= $weeklyAttr && $value <= $monthlyAttr;
            case 'weekly':
                $monthlyAttr = $this->requestArray['monthly_' . $attrNameWithoutPrefix];
                return $value <= $monthlyAttr;
            case 'monthly':
                break;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return t(self::ERROR_MESSAGES[$this->currentError], $this->errorMessageParams);
    }
}
