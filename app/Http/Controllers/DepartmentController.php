<?php
namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->get();
        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120|unique:departments,name',
            'color_hex' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
        ]);
        Department::create($data);
        return back()->with('status', 'Department added.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return back()->with('status', 'Department deleted.');
    }
}
