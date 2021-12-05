<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RepaymentTest extends TestCase
{
    /**
     * test case for repayment required fields
     *
     * @return void
     */
    public function test_repayment_required_fields()
    {
        $token = $this->getUserToken();

        $this->postJson(route('loan.repayment'),[
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "loan_id" => [
                    "The loan id field is required."
                ],
                "amount" => [
                    "The amount field is required."
                ]
            ]
        ]);
    }

    /**
     * test case for repayment loan does not exist
     *
     * @return void
     */
    public function test_repayment_loan_not_exist()
    {
        $token = $this->getUserToken();

        $this->postJson(route('loan.repayment'),[
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'loan_id'   => 9999999,
            'amount'   => 1000,
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => "Loan does not exist, Please check the detail you entered."
        ]);
    }

    /**
     * test case for repayment loan not active
     *
     * @return void
     */
    public function test_repayment_loan_not_active()
    {
        $token = $this->getUserToken();

        $this->postJson(route('loan.repayment'),[
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'loan_id'   => $this->getLoanId(),
            'amount'   => 1000,
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => "Loan is not active, Please user the loan status change API to change the status to active."
        ]);
    }

    /**
     * test case for repayment loan less than actual emi amount
     *
     * @return void
     */
    public function test_repayment_loan_less_emi_amount()
    {
        $token = $this->getUserToken();

        $loanId = $this->getLoanId();

        $loanStatus = $this->postJson(route('loan.status-change'),[
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'loan_id'   => $loanId,
            'status'   => 'active',
        ]);

        if (json_decode($loanStatus->getContent())->success)
        {
            $this->postJson(route('loan.repayment'),[
                'headers' => [
                    'Authorization' => 'Bearer '. $token,
                ],
                'loan_id'   => $loanId,
                'amount'   => 900,
            ])->assertJson([
                "success" => false,
                "message" => "Error",
                "data" => "Loan EMI amount is less than as compare with actual EMI."
            ]);
        }
    }

    /**
     * test case for repayment email paid successfully
     *
     * @return void
     */
    public function test_repayment_loan_emi_successfully_paid()
    {
        $token = $this->getUserToken();

        $loanId = $this->getLoanId();

        $loanStatus = $this->postJson(route('loan.status-change'),[
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'loan_id'   => $loanId,
            'status'   => 'active',
        ]);

        if (json_decode($loanStatus->getContent())->success)
        {
            $this->postJson(route('loan.repayment'),[
                'headers' => [
                    'Authorization' => 'Bearer '. $token,
                ],
                'loan_id'   => $loanId,
                'amount'   => 1000,
            ])->assertJson([
                "success" => true,
                "data" => "Success",
                "message" => "Payment has been done successfully."
            ]);
        }
    }

    /**
     * test case for repayment loan recovered successfully
     *
     * @return void
     */
    public function test_repayment_loan_recovered_successfully()
    {
        $token = $this->getUserToken();

        $loanId = $this->getLoanId(true);

        $loanStatus = $this->postJson(route('loan.status-change'),[
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'loan_id'   => $loanId,
            'status'   => 'active',
        ]);

        if (json_decode($loanStatus->getContent())->success)
        {
            $this->postJson(route('loan.repayment'),[
                'headers' => [
                    'Authorization' => 'Bearer '. $token,
                ],
                'loan_id'   => $loanId,
                'amount'   => 1000,
            ]);


            $this->postJson(route('loan.repayment'),[
                'headers' => [
                    'Authorization' => 'Bearer '. $token,
                ],
                'loan_id'   => $loanId,
                'amount'   => 1000,
            ])->assertJson([
                "success" => true,
                "data" => "Success",
                "message" => "Loan amount has been fully recovered."
            ]);
        }
    }

    /**
     * @return mixed
     *
     * This method is return the token code of the user.
     */
    private function getUserToken()
    {
        $user = $this->postJson(route('api.login'), [
            'email' => 'sample@example.com',
            'password' => 'password',
        ]);

        return json_decode($user->getContent())->data->token;
    }

    /**
     * @return mixed
     *
     * This method is return the token code of the user.
     */
    private function getLoanId($flag = false)
    {
        $token = $this->getUserToken();

        $loan = $this->postJson(route('loan.store'),[
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'amount'    =>  ($flag) ? '1000' : '10000',
            'term'    =>  ($flag) ? '1' : '10'
        ]);

        return json_decode($loan->getContent())->data->Id;
    }
}
