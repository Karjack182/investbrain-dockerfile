<?php

namespace App\Http\Requests;

use App\Models\Portfolio;
use App\Http\Requests\FormRequest;
use App\Rules\SymbolValidationRule;
use App\Rules\QuantityValidationRule;

class TransactionRequest extends FormRequest
{

    public ?Portfolio $portfolio;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $this->portfolio = Portfolio::findOrFail($this->requestOrModelValue('portfolio_id', 'transaction'));

        $rules = [
            'portfolio_id' => [], // validated by findOrFail() above
            'symbol' => ['required', 'string', new SymbolValidationRule],
            'transaction_type' => ['required', 'string', 'in:BUY,SELL'],
            'date' => ['required', 'date_format:Y-m-d', 'before_or_equal:' . now()->format('Y-m-d')],
            'quantity' => [
                'required', 
                'numeric', 
                'min:0', 
                new QuantityValidationRule(
                    $this->portfolio,
                    $this->requestOrModelValue('symbol', 'transaction'),
                    $this->requestOrModelValue('transaction_type', 'transaction'),
                    $this->requestOrModelValue('date', 'transaction')
                )
            ],
            'cost_basis' => ['exclude_if:transaction_type,SELL', 'min:0', 'numeric'],
            'sale_price' => ['exclude_if:transaction_type,BUY', 'min:0', 'numeric'],
        ];

        if (!is_null($this->transaction)) {
            $rules['symbol'][0] = 'sometimes';
            $rules['transaction_type'][0] = 'sometimes';
            $rules['date'][0] = 'sometimes';
            $rules['quantity'][0] = 'sometimes';

            if (
                $this->requestOrModelValue('transaction_type', 'transaction') == 'SELL'
                && $this->requestOrModelValue('sale_price', 'transaction') == null
            ) {
                $rules['sale_price'][0] = 'required';
            } elseif (
                $this->requestOrModelValue('transaction_type', 'transaction') == 'BUY'
                && $this->requestOrModelValue('cost_basis', 'transaction') == null
            ) {
                $rules['cost_basis'][0] = 'required';
            }
        } 

        return $rules;
    }
}
