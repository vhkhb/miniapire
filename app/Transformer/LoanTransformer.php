<?php

namespace App\Transformer;

use App\Models\Loans;
use Carbon\Carbon;
use League\Fractal;


class LoanTransformer extends Fractal\TransformerAbstract
{
    /**
     * @param Loans $loan
     * @return array
     *
     * This method is use for the transform object to Array for loan data
     */
    public function transform(Loans $loan)
    {
        return [
            'Id'        => (int) $loan->id,
            'Amount'    => (double) $loan->amount,
            'Term'      => (int) $loan->term,
            'RePaid'    => (int) $loan->term - $loan->repay_count,
            'Status'    => (string) $loan->status,
            'CreatedAt' => $loan->created_at->format('d-m-Y'),
            'User'      => (new UserTransformer())->transform($loan->user),
        ];
    }
}
