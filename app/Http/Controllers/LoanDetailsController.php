<?php

namespace App\Http\Controllers;

use App\Models\Loans;
use App\Models\Repayments;
use App\Transformer\LoanTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanDetailsController extends CommonController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validation for the request parameters
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'term' => 'required|numeric|min:1'
        ]);

        if($validator->fails()){
            return $this->sendError(__('messages.error'), $validator->errors());
        }

        // create new loan row in database
        $loan = new Loans();
        $loan->user_id = auth()->user()->id;
        $loan->amount = $request->get('amount');
        $loan->term = $request->get('term');
        $loan->repay_count = $request->get('term');
        $loan->save();

        return $this->sendResponse((new LoanTransformer())->transform($loan), __('messages.loanRequestPlaced'));
    }

    public function loanStatusChange(Request $request)
    {
        // validation for the request parameters
        $validator = Validator::make($request->all(), [
            'loan_id' => 'required|numeric|min:1',
            'status' => 'required|in:active,inactive,pending'
        ]);

        if($validator->fails()) {
            return $this->sendError('Error', $validator->errors());
        }

        // update loan status from here.
        $loan = Loans::where('id', $request->get('loan_id'))->update(['status' => 'active']);

        if ($loan) {
            return $this->sendResponse(__('messages.success'), __('messages.loanApproved'));
        } else {
            return $this->sendError(__('messages.error'), __('messages.somethingWentWrong'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This method is use for the purpose of repayment of the loan.
     */
    public function repaymentLoan(Request $request)
    {
        // validation for the request parameters
        $validator = Validator::make($request->all(), [
            'loan_id' => 'required|numeric|min:1',
            'amount' => 'required|numeric|min:1'
        ]);

        if($validator->fails()) {
            return $this->sendError(__('messages.error'), $validator->errors());
        }

        $loan = Loans::where('id', $request->get('loan_id'))->where('user_id', auth()->user()->id);

        // validation to check loan exist or not
        if (!$loan->exists()) {
            return $this->sendError(__('messages.error'), __('messages.checkLoanDetail'));
        }

        // validation to check loan active or not
        if (!$loan->where('status', 'active')->exists()) {
            return $this->sendError(__('messages.error'), __('messages.loanNotActive'));
        }

        $dbLoan = $loan->first();
        $actuallyEMI = ($dbLoan->amount / $dbLoan->term);

        // validation to check loan emi is not less than actually emi
        if ($request->get('amount') < $actuallyEMI) {
            return $this->sendError(__('messages.error'), __('messages.loanEMIAmount'));
        }

        // validation to check loan already recovered or not
        if ($dbLoan->repay_count == 0) {
            return $this->sendResponse(__('messages.success'), __('messages.loanRecovered'));
        }

        // insert data in repayment table.
        $repayment = new Repayments();
        $repayment->loan_id = $request->get('loan_id');
        $repayment->repay_amount = $request->get('amount');
        $repayment->save();

        $repayment->loan->fill(['repay_count' => ($dbLoan->term - $dbLoan->repaymentCount())]);
        $repayment->loan->save();

        return $this->sendResponse(__('messages.success'), __('messages.paymentDone'));
    }
}
