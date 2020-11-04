<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Validator;
use App\Models\User;

class EmployeeController extends Controller
{
    public function add_employee(Request $request)
    {
        $company = Auth::user();
        if ($company['role'] !== "admin") {
            return response()->json([
                'success' => false,
                'error' => 'Only admins are allowed to add employees. Please contact an admin.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
            'gender' => 'required|in:male,female,other',
            'age' => 'required',
            'job_title' => 'required',
            'department' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        };

        $input = $request->all();
        $input["email"] = strtolower($input["email"]);
        $input['password'] = bcrypt(strtolower($input['last_name']));
        $input["role"] = "user";
        $input["company_id"] = $company["id"];
        $input["company_name"] = $company["company_name"];
        $input["company_address"] = $company["company_address"];

        $user = User::where('email', $input["email"])->get();
        if ($user->isEmpty()) {
            User::create($input);

            return response()->json([
                'success' => true,
                'message' => 'Employee Added! Password is employee last name (lower case only).',
            ], 201);
        };
        return response()->json([
            'success' => false,
            'error' => 'An account with that email already exist.'
        ], 400);
    }

    public function edit_employee(Request $request, $id)
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
        $input['first_name'] = (!$input['first_name']) ? $employee['first_name'] : $input["first_name"];
        $input['last_name'] = (!$input['last_name']) ? $employee['last_name'] : $input["last_name"];
        $input['email'] = (!$input['email']) ? $employee['email'] : strtolower($input["email"]);
        $input['phone'] = (!$input['phone']) ? $employee['phone'] : $input["phone"];
        $input['address'] = (!$input['address']) ? $employee['address'] : $input["address"];
        $input['gender'] = (!$input['gender']) ? $employee['gender'] : $input["gender"];
        $input['age'] = (!$input['age']) ? $employee['age'] : $input["age"];
        $input['job_title'] = (!$input['job_title']) ? $employee['job_title'] : $input["job_title"];
        $input['department'] = (!$input['department']) ? $employee['department'] : $input["department"];
        $input['password'] = bcrypt(strtolower($input['last_name']));

        $filltered = Arr::except($input, ['_method']);
        $updated = User::where('id', $id)->update($filltered);
        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Employee information updated successfully.',
            ], 200);
        }
        return response()->json([
            'success' => false,
            'error' => 'An internal server error occured.'
        ], 500);
    }

    public function single_employee($id)
    {
        $company = Auth::user();
        if ($company['role'] !== 'admin') {
            return response()->json([
                'success' => false,
                'error' => 'Not allowed.'
            ], 403);
        }

        $employee = User::where([
            ['role', 'user'],
            ['id', $id]
        ])->first();

        return response()->json([
            'success' => true,
            'employee' => $employee
        ], 200);
    }

    public function employee_profile()
    {
        $user = Auth::user();
        if ($user['role'] !== 'user') {
            return response()->json([
                'success' => false,
                'error' => 'Please login as an employee.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'employee' => $user
        ], 200);
    }

    public function salary()
    {
        $user = Auth::user();
        if ($user['role'] !== 'user') {
            return response()->json([
                'success' => false,
                'error' => 'Please login as an employee.'
            ], 403);
        }

        $salary = Payroll::where('user_id', $user['id'])->first();

        return response()->json([
            'success' => true,
            'salary' => $salary
        ], 200);
    }

    public function payslip(Request $request)
    {
        $user = Auth::user();
        if ($user['role'] !== 'user') {
            return response()->json([
                'success' => false,
                'error' => 'Please login as an employee.'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'month' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        };


        $payslip = Payslip::where([
            ['user_id', $user['id']],
            ['month', $request['month']]
        ])->first();
        if (!$payslip) {
            return response()->json([
                'success' => false,
                'error' => "Payslip not available yet."
            ], 404);
        }

        return response()->json([
            'success' => true,
            'payslip' => $payslip
        ], 200);
    }

    public function all_employees()
    {
        $company = Auth::user();
        $employees = User::where([
            ['role', 'user'],
            ['company_id', $company['id']],
        ])->orderBy('created_at')->simplePaginate(25);

        return response()->json([
            'success' => true,
            'employees' => $employees
        ], 201);
    }

    public function employee_reports()
    {
        $company = Auth::user();
        $report = User::where([
            ['role', 'user'],
            ['company_id', $company['id']],
        ])->select(
            'id',
            'first_name',
            'last_name',
            'job_title',
            'leave_status',
            'paye_status',
            'pension_status',
            'nsitf_status',
            'nhf_status'
        )->get();

        return response()->json([
            'success' => true,
            'reports' => $report
        ], 201);
     
    }
}
