<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoanTest extends TestCase
{
    /**
     * test case for new loan successfully
     *
     * @return void
     */
    public function test_new_loan_request_successfully()
    {
        $token = $this->getUserToken();

        $this->postJson(route('loan.store'), [
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'amount'    =>  '10000',
            'term'    =>  '10'
        ])->assertJsonStructure([
            "success",
            "data" => [
                "Id",
                "Amount",
                "Term",
                "RePaid",
                "Status",
                "CreatedAt",
                "User" => [
                    "Id",
                    "Name",
                    "Email",
                    "CreatedAt"
                ]
            ],
            "message"
        ]);;
    }

    /**
     * test case for new loan required fields
     *
     * @return void
     */
    public function test_new_loan_required_fields_validation()
    {
        $token = $this->getUserToken();

        $this->postJson(route('loan.store'), [
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "amount" => [
                    "The amount field is required."
                ],
                "term" => [
                    "The term field is required."
                ]
            ]
        ]);
    }

    /**
     * test case for new loan status change required fields
     *
     * @return void
     */
    public function test_new_loan_status_change_required_fields()
    {
        $token = $this->getUserToken();

        $this->postJson(route('loan.status-change'), [
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
                "status" => [
                    "The status field is required."
                ]
            ]
        ]);
    }

    /**
     * test case for new loan status change invalid status
     *
     * @return void
     */
    public function test_new_loan_status_change_invalid_status()
    {
        $token = $this->getUserToken();

        $this->postJson(route('loan.status-change'), [
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'loan_id'   => '2',
            'status'   => 'activedata',
        ])->assertJson([
            "success" => false,
            "message" => "Error",
            "data" => [
                "status" => [
                    "The selected status is invalid."
                ]
            ]
        ]);
    }

    /**
     * test case for new loan status change successfully
     *
     * @return void
     */
    public function test_new_loan_status_change_successfully()
    {
        $token = $this->getUserToken();

        $this->postJson(route('loan.status-change'), [
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'loan_id'   => $this->getLoanId(),
            'status'   => 'active',
        ])->assertJson([
            "success" => true,
            "data" => "Success",
            "message" => "Loan approved successfully."
        ]);
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
    private function getLoanId()
    {
        $token = $this->getUserToken();

        $loan = $this->postJson(route('loan.store'),[
            'headers' => [
                'Authorization' => 'Bearer '. $token,
            ],
            'amount'    =>  '10000',
            'term'    =>  '10'
        ]);

        return json_decode($loan->getContent())->data->Id;
    }
}
