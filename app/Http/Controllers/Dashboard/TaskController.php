<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskCompletion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->isAdmin(), 403);

        $payload = $this->validateTaskPayload($request);
        Task::create($payload + ['created_by' => $user->id]);

        return back()->with('status', 'Task assigned successfully.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->isAdmin(), 403);

        $payload = $this->validateTaskPayload($request);
        $task->update($payload);

        return back()->with('status', 'Task updated successfully.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->isAdmin(), 403);

        $task->delete();

        return back()->with('status', 'Task deleted.');
    }

    public function complete(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if (!$user->isAdmin() && $task->assigned_user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'completed_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $date = isset($validated['completed_on'])
            ? Carbon::parse($validated['completed_on'])->toDateString()
            : Carbon::now()->toDateString();

        TaskCompletion::firstOrCreate(
            [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'completed_on' => $date,
            ],
            [
                'notes' => $validated['notes'] ?? null,
            ],
        );

        return back()->with('status', 'Task marked as done.');
    }

    protected function validateTaskPayload(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'employee')),
            ],
            'recurrence_type' => ['required', Rule::in(array_keys(Task::recurrenceOptions()))],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'custom_weekdays' => ['nullable', 'array'],
            'custom_weekdays.*' => ['in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],
            'custom_interval_days' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validated['recurrence_type'] === Task::RECURRENCE_CUSTOM) {
            $request->validate([
                'custom_interval_days' => ['required', 'integer', 'min:1'],
            ]);
            $validated['custom_weekdays'] = $request->input('custom_weekdays', []);
            $validated['custom_interval_days'] = (int) $request->input('custom_interval_days');
        } else {
            $validated['custom_weekdays'] = [];
            $validated['custom_interval_days'] = null;
        }

        $validated['end_date'] = null;

        $validated['due_date'] = $validated['recurrence_type'] === Task::RECURRENCE_ONCE
            ? $validated['start_date']
            : null;

        return $validated;
    }
}
