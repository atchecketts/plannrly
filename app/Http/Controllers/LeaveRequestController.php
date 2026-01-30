<?php

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Http\Requests\Leave\ReviewLeaveRequestRequest;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Notifications\LeaveRequestStatusNotification;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'start_date' => 'start_date',
        'days' => 'total_days',
        'status' => 'status',
        'created' => 'created_at',
    ];

    private const GROUPABLE_COLUMNS = ['start_date', 'days', 'status', 'created'];

    public function index(Request $request): View
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isLocationAdmin() || $user->isDepartmentAdmin()) {
            $query = LeaveRequest::with(['user', 'leaveType', 'reviewedBy']);
        } else {
            $query = LeaveRequest::with(['leaveType', 'reviewedBy'])
                ->where('user_id', $user->id);
        }

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getLeaveRequestGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'created', 'desc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $leaveRequests = $query->get();
        } else {
            $leaveRequests = $query->paginate(15)->withQueryString();
        }

        return view('leave.index', compact('leaveRequests', 'sortParams', 'allGroups'));
    }

    private function getLeaveRequestGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        return match ($group) {
            'start_date' => $this->getAllGroupValues($query, 'start_date', fn ($date) => [
                'key' => 'start_date-'.$date->format('Y-m-d'),
                'label' => $date->format('M d, Y'),
            ])->unique('key')->values()->toArray(),
            'days' => $this->getAllGroupValues($query, 'total_days', fn ($days) => [
                'key' => 'days-'.$days,
                'label' => $days.' '.str('Day')->plural($days),
            ])->toArray(),
            'status' => collect(LeaveRequestStatus::cases())->map(fn ($status) => [
                'key' => 'status-'.$status->value,
                'label' => $status->label(),
            ])->toArray(),
            'created' => $this->getAllGroupValues($query, 'created_at', fn ($date) => [
                'key' => 'created-'.$date->format('Y-m-d'),
                'label' => $date->format('M d, Y'),
            ])->unique('key')->values()->toArray(),
            default => [],
        };
    }

    public function create(): View
    {
        $leaveTypes = LeaveType::active()
            ->forTenant(auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('leave.create', compact('leaveTypes'));
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        $leaveRequest = new LeaveRequest([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'start_half_day' => $data['start_half_day'] ?? false,
            'end_half_day' => $data['end_half_day'] ?? false,
            'reason' => $data['reason'] ?? null,
            'status' => LeaveRequestStatus::Draft,
        ]);

        $leaveRequest->total_days = $leaveRequest->calculateTotalDays();
        $leaveRequest->save();

        if ($request->boolean('submit')) {
            $leaveRequest->submit();
        }

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Leave request created successfully.');
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->authorize('view', $leaveRequest);

        $leaveRequest->load(['user', 'leaveType', 'reviewedBy']);

        return view('leave.show', compact('leaveRequest'));
    }

    public function submit(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('update', $leaveRequest);

        $leaveRequest->submit();

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Leave request submitted for approval.');
    }

    public function review(ReviewLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('review', $leaveRequest);

        if ($request->isApproval()) {
            $leaveRequest->approve(auth()->user(), $request->input('review_notes'));
            $message = 'Leave request approved.';
        } else {
            $leaveRequest->reject(auth()->user(), $request->input('review_notes'));
            $message = 'Leave request rejected.';
        }

        // Notify the user of their leave request status
        $leaveRequest->load('user');
        $leaveRequest->user->notify(new LeaveRequestStatusNotification($leaveRequest));

        return redirect()
            ->route('leave-requests.index')
            ->with('success', $message);
    }

    public function destroy(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('delete', $leaveRequest);

        $leaveRequest->delete();

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Leave request cancelled.');
    }
}
