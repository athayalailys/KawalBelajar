<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Subject::create($data);

        return back()->with('status', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $subject = Subject::findOrFail($id);
        $subject->update($this->validated($request, $subject));

        return back()->with('status', 'Mata pelajaran berhasil diperbarui.');
    }

    public function toggle(string $id): RedirectResponse
    {
        $subject = Subject::findOrFail($id);
        $subject->update(['is_active' => ! $subject->is_active]);

        return back()->with('status', 'Status mata pelajaran berhasil diperbarui.');
    }

    private function validated(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('subjects', 'name')->where('grade_level', $request->input('grade_level'))->ignore($subject?->subject_id, 'subject_id')],
            'grade_level' => ['required', Rule::in(['SD', 'SMP', 'SMA', 'UMUM'])],
            'category' => ['required', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
