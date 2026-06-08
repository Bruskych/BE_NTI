<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Application;
use App\Actions\ApproveApplicationAction;
use App\Actions\RejectApplicationAction;
use App\Http\Resources\StudentApplicationResource;
use App\Http\Resources\CompanyApplicationResource;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'users_count' => User::count(),
            'pending_applications_count' => Application::where('status', 'submitted')->count(),
            'latest_users' => User::with('roles')->latest()->take(5)->get(),
        ]);
    }

    public function pendingStudents()
    {
        $applications = Application::where('status', 'submitted')
            ->whereNull('organization_id')
            ->whereNull('deleted_at')
            ->with('team.leader')
            ->get();

        return StudentApplicationResource::collection($applications);
    }

    public function pendingCompanies()
    {
        $applications = Application::where('status', 'submitted')
            ->whereNotNull('organization_id')
            ->with(['organization', 'team.leader'])
            ->get();

        return CompanyApplicationResource::collection($applications);
    }

    public function approveStudent($id, Request $request, ApproveApplicationAction $action)
    {
        $application = Application::findOrFail($id);
        $action->execute($application, $request->comment ?? 'Approved', 'student', $request->user()->id);
        return response()->json(['message' => 'Student approved successfully.']);
    }

    public function rejectStudent($id, Request $request, RejectApplicationAction $action)
    {
        $application = Application::findOrFail($id);
        $action->execute($application, $request->comment ?? 'Rejected', $request->user()->id);
        return response()->json(['message' => 'Student rejected successfully.']);
    }

    public function approveCompany($id, Request $request, ApproveApplicationAction $action)
    {
        $application = Application::with('organization')->findOrFail($id);
        $action->execute($application, $request->comment ?? 'Approved', 'company', $request->user()->id);
        return response()->json(['message' => 'Company approved successfully.']);
    }

    public function rejectCompany($id, Request $request, RejectApplicationAction $action)
    {
        $application = Application::findOrFail($id);
        $action->execute($application, $request->comment ?? 'Rejected', $request->user()->id);
        return response()->json(['message' => 'Company rejected successfully.']);
    }
}
