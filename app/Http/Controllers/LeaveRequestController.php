<?php

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Http\Requests\Leave\ReviewLeaveRequestRequest;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isLocationAdmin() || $user->isDepartmentAdmin()) {
            $leaveRequests = LeaveRequest::with(['user', 'leaveType', 'reviewedBy'])
                ->orderByDesc('created_at')
                ->paginate(15);
        } else {
            $leaveRequests = LeaveRequest::with(['leaveType', 'reviewedBy'])
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return view('leave.index', compact('leaveRequests'));
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

        $user = auth()->user();
        $route = $user->isEmployee() ? 'my-leave.index' : 'leave-requests.index';

        return redirect()
            ->route($route)
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

    public function myCreate(): View
    {
        $leaveTypes = LeaveType::active()
            ->forTenant(auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('my-leave.create', compact('leaveTypes'));
    }

    public function myRequests(): View
    {
        $user = auth()->user();

        $leaveRequests = LeaveRequest::with(['leaveType', 'reviewedBy'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $pendingRequests = $leaveRequests->filter(
            fn ($request) => $request->status === LeaveRequestStatus::Requested
        );

        $upcomingApproved = LeaveRequest::with(['leaveType'])
            ->where('user_id', $user->id)
            ->where('status', LeaveRequestStatus::Approved)
            ->where('start_date', '>=', today())
            ->orderBy('start_date')
            ->get();

        return view('my-leave.index', [
            'leaveRequests' => $leaveRequests,
            'pendingRequests' => $pendingRequests,
            'upcomingApproved' => $upcomingApproved,
        ]);
    }

    public function mobile(): View
    {
        $user = auth()->user();

        $status = request()->query('status', 'pending');

        $query = LeaveRequest::with(['user', 'leaveType', 'reviewedBy'])
            ->orderByDesc('created_at');

        if ($status === 'pending') {
            $query->where('status', LeaveRequestStatus::Requested);
        } elseif ($status === 'approved') {
            $query->where('status', LeaveRequestStatus::Approved);
        } elseif ($status === 'rejected') {
            $query->where('status', LeaveRequestStatus::Rejected);
        }

        $leaveRequests = $query->paginate(15);

        $pendingCount = LeaveRequest::where('status', LeaveRequestStatus::Requested)->count();

        return view('leave-requests.admin-mobile-index', [
            'leaveRequests' => $leaveRequests,
            'status' => $status,
            'pendingCount' => $pendingCount,
        ]);
    }
}
