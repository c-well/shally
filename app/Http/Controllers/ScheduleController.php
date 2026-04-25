<?php
namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\DutyAssignment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    private array $scheduleDeptNames = ['Ushers', 'Deacons', 'Music', 'Communication', 'Platform Party'];

    /** GET /schedule — render the editor shell. */
    public function index(Request $request)
    {
        $departments = Department::whereIn('name', $this->scheduleDeptNames)->get()
            ->sortBy(fn ($d) => array_search($d->name, $this->scheduleDeptNames))->values();
        $deptId = $request->integer('dept') ?: ($departments->first()?->id);
        $monthIso = $request->query('month') ?: now()->startOfMonth()->toDateString();
        return view('schedule', compact('departments', 'deptId', 'monthIso'));
    }

    /** GET /api/schedule/{dept}?month=YYYY-MM-DD — JSON for the editor to render. */
    public function data(Request $request, int $deptId): JsonResponse
    {
        $monthIso = $request->query('month') ?: now()->startOfMonth()->toDateString();
        $monthStart = Carbon::parse($monthIso)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // All Saturdays in this month (1-5 of them)
        $saturdays = [];
        $cur = $monthStart->copy();
        while ($cur->dayOfWeek !== Carbon::SATURDAY) $cur->addDay();
        while ($cur->lte($monthEnd)) {
            $saturdays[] = $cur->toDateString();
            $cur->addWeek();
        }

        $department = Department::findOrFail($deptId);
        $members = $department->members()->where('is_active', true)->get();
        $assignments = $department->assignments()
            ->whereBetween('week_date', [$monthStart, $monthEnd])
            ->orderBy('sort_order')->get();

        return response()->json([
            'department'        => $department->only(['id', 'name', 'color_hex']),
            'is_platform_party' => $department->name === 'Platform Party',
            'month_label'       => $monthStart->format('F Y'),
            'month_iso'         => $monthStart->toDateString(),
            'prev_month'        => $monthStart->copy()->subMonth()->toDateString(),
            'next_month'        => $monthStart->copy()->addMonth()->toDateString(),
            'saturdays'         => $saturdays,
            'members'           => $members->map(fn ($m) => $m->only(['id', 'name'])),
            'assignments'       => $assignments->map(fn ($a) => [
                'id'         => $a->id,
                'week_date'  => $a->week_date->format('Y-m-d'),
                'member_id'  => $a->member_id,
                'role'       => $a->role,
                'text_value' => $a->text_value,
                'sort_order' => $a->sort_order,
            ]),
        ]);
    }

    public function storeMember(Request $request, int $deptId): JsonResponse
    {
        $dept = Department::findOrFail($deptId);
        $data = $request->validate(['name' => 'required|string|max:180']);
        $maxSort = $dept->members()->max('sort_order') ?? -1;
        $m = $dept->members()->create($data + ['sort_order' => $maxSort + 1, 'is_active' => true]);
        return response()->json(['ok' => true, 'member' => $m->only(['id', 'name'])]);
    }

    public function updateMember(Request $request, DepartmentMember $member): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'sometimes|string|max:180',
            'is_active' => 'sometimes|boolean',
        ]);
        $member->update($data);
        return response()->json(['ok' => true]);
    }

    public function deleteMember(DepartmentMember $member): JsonResponse
    {
        $member->delete();
        return response()->json(['ok' => true]);
    }

    public function storeAssignment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'week_date'     => 'required|date',
            'member_id'     => 'nullable|exists:department_members,id',
            'role'          => 'nullable|string|max:80',
            'text_value'    => 'nullable|string|max:240',
        ]);
        $maxSort = DutyAssignment::where('department_id', $data['department_id'])
            ->where('week_date', $data['week_date'])->max('sort_order') ?? -1;
        $a = DutyAssignment::create($data + ['sort_order' => $maxSort + 1]);
        return response()->json(['ok' => true, 'assignment' => [
            'id'         => $a->id,
            'week_date'  => $a->week_date->format('Y-m-d'),
            'member_id'  => $a->member_id,
            'role'       => $a->role,
            'text_value' => $a->text_value,
            'sort_order' => $a->sort_order,
        ]]);
    }

    public function updateAssignment(Request $request, DutyAssignment $assignment): JsonResponse
    {
        $data = $request->validate([
            'member_id'  => 'sometimes|nullable|exists:department_members,id',
            'role'       => 'sometimes|nullable|string|max:80',
            'text_value' => 'sometimes|nullable|string|max:240',
        ]);
        $assignment->update($data);
        return response()->json(['ok' => true]);
    }

    public function deleteAssignment(DutyAssignment $assignment): JsonResponse
    {
        $assignment->delete();
        return response()->json(['ok' => true]);
    }
}
