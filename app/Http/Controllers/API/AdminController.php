<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Validator;
use App\Models\User;
use App\Models\Payroll;
use App\Models\Payslip;


class AdminController extends Controller
{
    public function create_payroll(Request $request, $id)
    {
        $company = Auth::user();
        if ($company['role'] !== "admin") {
            return response()->json([
                'success' => false,
                'error' => 'Only admins are allowed to add employees. Please contact an admin.'
            ], 403);
        }
        $employee = User::where([
            ['role', 'user'],
            ['id', $id],
        ])->first();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid employee ID'
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'basic_salary' => 'required',
            'paye' => 'required',
            'pension' => 'required',
            'nsitf' => 'required',
            'nhf' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        };
        $input = $request->all();

        // calculate deductions and net salary
        $gross_salary = $input["basic_salary"] + $input["housing"] + $input["transportation"];
        $tax = ($input["paye"] / 100) * $gross_salary;
        $pension = ($input["pension"] / 100) * $gross_salary;
        $nsitf = ($input["nsitf"] / 100) * $gross_salary;
        $nhf = ($input["nhf"] / 100) * $gross_salary;

        $input['deductions'] = $tax + $pension + $nsitf + $nhf;
        $input['net_salary'] = $gross_salary - $input['deductions'];
        $input['user_id'] = $employee['id'];

        $payroll = Payroll::where('user_id', $id)->first();
        if (!$payroll) {
            $new_payroll = Payroll::create($input);
            if ($new_payroll) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payroll successfully added.',
                ], 201);
            }
            return response()->json([
                'success' => false,
                'error' => 'An internal server error occured.'
            ], 500);
        }
        $updated = Payroll::where('user_id', $employee['id'])->update($input);
        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Payroll updated successfully.',
            ], 201);
        }
        return response()->json([
            'success' => false,
            'error' => 'An internal server error occured.'
        ], 500);
    }

    public function employee_payroll($id)
    {
        $company = Auth::user();
        if ($company['role'] !== "admin") {
            return response()->json([
                'success' => false,
                'error' => 'Only admins are allowed to add employees. Please contact an admin.'
            ], 403);
        }
        $employee = User::where([
            ['role', 'user'],
            ['id', $id],
        ])->first();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid employee ID'
            ], 404);
        }

        $payroll = Payroll::where('user_id', $id)->first();
        return response()->json([
            'success' => true,
            'payroll' => $payroll
        ], 200);
    }
    public function payslip(Request $request, $id)
    {
        $company = Auth::user();
        if ($company['role'] !== "admin") {
            return response()->json([
                'success' => false,
                'error' => 'Not authorized.'
            ], 403);
        }
        $employee = User::where([
            ['role', 'user'],
            ['id', $id],
        ])->first();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid employee ID'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        };
        $input = $request->all();
        $payroll = Payroll::where('user_id', $id)->first();
        if(!$payroll){
            return response()->json([
                'success' => false,
                'error' => 'Please add employee to payroll before generating payslip.',
                'input' => $input
            ], 400);
        }
        $gross_salary = $payroll["basic_salary"] + $payroll["housing"] + $payroll["transportation"];
        $tax = ($payroll["paye"] / 100) * $gross_salary;
        $pension = ($payroll["pension"] / 100) * $gross_salary;
        $nsitf = ($payroll["nsitf"] / 100) * $gross_salary;
        $nhf = ($payroll["nhf"] / 100) * $gross_salary;

        // retrieve existing payslip
        $user_payslip = Payslip::where([
            ['user_id', $id],
            ['month', $input['month']]
        ])->first();
        if (!$user_payslip) {
            // create payslip
            $input['basic_salary'] = $payroll["basic_salary"];
            $input['net_salary'] = $payroll["net_salary"];
            $input['housing'] = $payroll["housing"];
            $input['transportation'] = $payroll["transportation"];
            $input['paye_amount'] = $tax;
            $input['pension_amount'] = $pension;
            $input['nsitf_amount'] = $nsitf;
            $input['nhf_amount'] = $nhf;
            $input['total_deductions'] = $payroll["deductions"];
            $input['total_earnings'] = $gross_salary;
            $input['user_id'] = $id;
            $new_payslip = Payslip::create($input);
            if ($new_payslip) {
                return response()->json([
                    'success' => true,
                    'payslip' => $new_payslip,
                    'company_name' => $employee['company_name'],
                    'company_address' => $employee['company_address'],
                    'employee_first_name' => $employee['first_name'],
                    'employee_last_name' => $employee['last_name'],
                    'employee_job_title' => $employee['job_title'],
                ], 200);
            }
            return response()->json([
                'success' => false,
                'error' => 'An internal server error occured.'
            ], 500);
        }
        return response()->json([
            'success' => true,
            'payslip' => $user_payslip,
            'company_name' => $employee['company_name'],
            'company_address' => $employee['company_address'],
            'employee_first_name' => $employee['first_name'],
            'employee_last_name' => $employee['last_name'],
            'employee_job_title' => $employee['job_title'],
        ], 200);
    }

    public function remittances(Request $request, $id)
    {
        $company = Auth::user();
        if ($company['role'] !== "admin") {
            return response()->json([
                'success' => false,
                'error' => 'Only admins are allowed to add employees. Please contact an admin.'
            ], 403);
        }
        $employee = User::where([
            ['role', 'user'],
            ['id', $id],
        ])->first();
        if (!$employee) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid employee ID'
            ], 404);
        }
        $input = $request->all();
        if ($input['query'] === 'tax') {
            $employee->paye_status = 'Paid';
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Employee tax successfully remitted.'
            ], 200);
        }
        if ($input['query'] === 'pension') {
            $employee->pension_status = 'Paid';
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Employee pension successfully remitted.'
            ], 200);
        }
        if ($input['query'] === 'nsitf') {
            $employee->nsitf_status = 'Paid';
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Employee NSITF successfully remitted.'
            ], 200);
        }
        if ($input['query'] === 'nhf') {
            $employee->nhf_status = 'Paid';
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Employee NHF successfully remitted.'
            ], 200);
        }
    }


   
}
