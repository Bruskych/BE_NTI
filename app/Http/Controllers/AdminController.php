<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Application;
use App\Actions\ApproveApplicationAction;
use App\Actions\RejectApplicationAction;
use App\Http\Resources\StudentApplicationResource;
use App\Http\Resources\CompanyApplicationResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/** Контроллер административной панели: статистика, одобрение и отклонение заявок */
class AdminController extends Controller
{
    /** Возвращает сводную статистику для дашборда администратора */
    #[OA\Get(
        path: '/admin/dashboard',
        summary: '[Admin] Get dashboard summary stats (user count, pending applications, latest users)',
        tags: ['Admin'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Dashboard summary'),
        ]
    )]
    public function dashboard(): \Illuminate\Http\JsonResponse
    {
        return $this->apiJson([
            'users_count' => User::count(),
            'pending_applications_count' => Application::where('status', 'submitted')->count(),
            'latest_users' => User::with('roles')->latest()->take(5)->get(),
        ]);
    }

    /** Возвращает список студенческих заявок, ожидающих рассмотрения */
    #[OA\Get(
        path: '/admin/students/pending',
        summary: '[Admin] List submitted student applications awaiting review',
        tags: ['Admin'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of pending student applications'),
        ]
    )]
    public function pendingStudents()
    {
        $applications = Application::where('status', 'submitted')
            ->whereNull('organization_id')
            ->with('team.leader')
            ->get();

        return StudentApplicationResource::collection($applications);
    }

    /** Возвращает список заявок компаний, ожидающих рассмотрения */
    #[OA\Get(
        path: '/admin/companies/pending',
        summary: '[Admin] List submitted company applications awaiting review',
        tags: ['Admin'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of pending company applications'),
        ]
    )]
    public function pendingCompanies()
    {
        $applications = Application::where('status', 'submitted')
            ->whereNotNull('organization_id')
            ->with(['organization', 'team.leader'])
            ->get();

        return CompanyApplicationResource::collection($applications);
    }

    /** Одобряет студенческую заявку по идентификатору */
    #[OA\Post(
        path: '/admin/students/{id}/approve',
        summary: '[Admin] Approve a pending student application',
        tags: ['Admin'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Application ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Student approved'),
            new OA\Response(response: 404, description: 'Application not found'),
        ]
    )]
    public function approveStudent(int $id, Request $request, ApproveApplicationAction $action)
    {
        $application = Application::findOrFail($id);
        $action->execute($application, $request->comment ?? 'Approved', 'student', $request->user()->id);
        return $this->apiJson(['message' => 'Student approved successfully.']);
    }

    /** Отклоняет студенческую заявку по идентификатору */
    #[OA\Post(
        path: '/admin/students/{id}/reject',
        summary: '[Admin] Reject a pending student application',
        tags: ['Admin'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Application ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Student rejected'),
            new OA\Response(response: 404, description: 'Application not found'),
        ]
    )]
    public function rejectStudent(int $id, Request $request, RejectApplicationAction $action)
    {
        $application = Application::findOrFail($id);
        $action->execute($application, $request->comment ?? 'Rejected', $request->user()->id);
        return $this->apiJson(['message' => 'Student rejected successfully.']);
    }

    /** Одобряет заявку компании по идентификатору */
    #[OA\Post(
        path: '/admin/companies/{id}/approve',
        summary: '[Admin] Approve a pending company application',
        tags: ['Admin'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Application ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Company approved'),
            new OA\Response(response: 404, description: 'Application not found'),
        ]
    )]
    public function approveCompany(int $id, Request $request, ApproveApplicationAction $action)
    {
        $application = Application::with('organization')->findOrFail($id);
        $action->execute($application, $request->comment ?? 'Approved', 'company', $request->user()->id);
        return $this->apiJson(['message' => 'Company approved successfully.']);
    }

    /** Отклоняет заявку компании по идентификатору */
    #[OA\Post(
        path: '/admin/companies/{id}/reject',
        summary: '[Admin] Reject a pending company application',
        tags: ['Admin'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Application ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Company rejected'),
            new OA\Response(response: 404, description: 'Application not found'),
        ]
    )]
    public function rejectCompany(int $id, Request $request, RejectApplicationAction $action)
    {
        $application = Application::findOrFail($id);
        $action->execute($application, $request->comment ?? 'Rejected', $request->user()->id);
        return $this->apiJson(['message' => 'Company rejected successfully.']);
    }
}
