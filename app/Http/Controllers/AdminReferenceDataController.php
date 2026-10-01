<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\Institution;
use App\Models\Nationality;
use App\Models\Specialization;
use App\Models\StudyLevel;
use App\Models\TrainingCompletionStatus;
use App\Models\TrainingReportStatus;
use App\Models\TrainingType;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminReferenceDataController extends Controller
{
    /**
     * @return array<string, array{model: class-string<Model>, title: string, singular: string, description: string, icon: string, form: string, code_required: bool, columns: array<string, string>}>
     */
    private function resources(): array
    {
        return [
            'departments' => [
                'model' => Department::class,
                'title' => 'Departments',
                'singular' => 'department',
                'description' => 'Academic and review departments used for application routing.',
                'icon' => 'fa-building',
                'form' => 'basic',
                'code_required' => false,
                'columns' => ['name' => 'Department', 'code' => 'Code'],
            ],
            'training-types' => [
                'model' => TrainingType::class,
                'title' => 'Training Types',
                'singular' => 'training type',
                'description' => 'Program categories available when configuring application windows.',
                'icon' => 'fa-briefcase',
                'form' => 'training-type',
                'code_required' => true,
                'columns' => ['name' => 'Training type', 'code' => 'Code', 'description' => 'Description'],
            ],
            'institutions' => [
                'model' => Institution::class,
                'title' => 'Institutions',
                'singular' => 'institution',
                'description' => 'Institutions available during student registration and profile updates.',
                'icon' => 'fa-university',
                'form' => 'basic',
                'code_required' => false,
                'columns' => ['name' => 'Institution', 'code' => 'Code'],
            ],
            'courses' => [
                'model' => Course::class,
                'title' => 'Courses',
                'singular' => 'course',
                'description' => 'Academic courses assigned to student profiles.',
                'icon' => 'fa-graduation-cap',
                'form' => 'basic',
                'code_required' => true,
                'columns' => ['name' => 'Course', 'code' => 'Code'],
            ],
            'study-levels' => [
                'model' => StudyLevel::class,
                'title' => 'Study Levels',
                'singular' => 'study level',
                'description' => 'Qualification levels available to students.',
                'icon' => 'fa-layer-group',
                'form' => 'basic',
                'code_required' => true,
                'columns' => ['name' => 'Study level', 'code' => 'Code'],
            ],
            'nationalities' => [
                'model' => Nationality::class,
                'title' => 'Nationalities',
                'singular' => 'nationality',
                'description' => 'Nationality options used in student records.',
                'icon' => 'fa-globe-africa',
                'form' => 'nationality',
                'code_required' => false,
                'columns' => ['name' => 'Nationality', 'code' => 'ISO code'],
            ],
            'specializations' => [
                'model' => Specialization::class,
                'title' => 'Specializations',
                'singular' => 'specialization',
                'description' => 'Specialization names and codes for future academic filtering.',
                'icon' => 'fa-stream',
                'form' => 'basic',
                'code_required' => true,
                'columns' => ['name' => 'Specialization', 'code' => 'Code'],
            ],
            'document-types' => [
                'model' => DocumentType::class,
                'title' => 'Document Types',
                'singular' => 'document type',
                'description' => 'Required files, accepted formats, and upload limits for applications.',
                'icon' => 'fa-file-alt',
                'form' => 'document-type',
                'code_required' => true,
                'columns' => ['name' => 'Document type', 'code' => 'Code', 'is_required' => 'Requirement', 'is_active' => 'Status'],
            ],
            'training-report-statuses' => [
                'model' => TrainingReportStatus::class,
                'title' => 'Training Report Statuses',
                'singular' => 'report status',
                'description' => 'Configurable statuses for future training report tracking.',
                'icon' => 'fa-clipboard-check',
                'form' => 'basic',
                'code_required' => true,
                'columns' => ['name' => 'Status', 'code' => 'Code'],
            ],
            'training-completion-statuses' => [
                'model' => TrainingCompletionStatus::class,
                'title' => 'Completion Statuses',
                'singular' => 'completion status',
                'description' => 'Configurable outcomes for future training completion tracking.',
                'icon' => 'fa-flag-checkered',
                'form' => 'basic',
                'code_required' => true,
                'columns' => ['name' => 'Status', 'code' => 'Code'],
            ],
        ];
    }

    public function dashboard(): View
    {
        $resources = collect($this->resources())
            ->map(fn (array $resource, string $type): array => [
                'type' => $type,
                'count' => $resource['model']::query()->count(),
            ] + $resource)
            ->values()
            ->all();

        return view('admin.reference-data.dashboard', compact('resources'));
    }

    public function index(Request $request, string $type): View
    {
        $resource = $this->resource($type);
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $model = $resource['model'];
        $records = $model::query()
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reference-data.index', [
            'resource' => $resource,
            'resourceType' => $type,
            'records' => $records,
            'search' => $validated['search'] ?? '',
        ]);
    }

    public function edit(string $record, string $type): View
    {
        $resource = $this->resource($type);
        $recordModel = $resource['model']::findOrFail($record);

        return view('admin.reference-data.edit', [
            'resource' => $resource,
            'resourceType' => $type,
            'record' => $recordModel,
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $resource = $this->resource($type);
        $data = $this->validatedData($request, $resource, null);
        $model = $resource['model'];
        $record = $model::create($data);

        AuditLogger::record($type.'.created', $record, null, $record->toArray());

        return back()->with('success', ucfirst($resource['singular']).' added successfully.');
    }

    public function update(Request $request, string $record, string $type): RedirectResponse
    {
        $resource = $this->resource($type);
        $model = $resource['model'];
        $recordModel = $model::findOrFail($record);
        $oldValues = $recordModel->toArray();
        $data = $this->validatedData($request, $resource, $recordModel);
        $recordModel->update($data);

        AuditLogger::record($type.'.updated', $recordModel, $oldValues, $recordModel->fresh()->toArray());

        return redirect()->route('admin.reference-data.'.$type.'.index')->with('success', ucfirst($resource['singular']).' updated successfully.');
    }

    /**
     * @return array{model: class-string<Model>, title: string, singular: string, description: string, icon: string, form: string, code_required: bool, columns: array<string, string>}
     */
    private function resource(string $type): array
    {
        $resources = $this->resources();

        abort_unless(array_key_exists($type, $resources), 404);

        return $resources[$type];
    }

    /**
     * @param  array{model: class-string<Model>, title: string, singular: string, description: string, icon: string, form: string, code_required: bool, columns: array<string, string>}  $resource
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, array $resource, ?Model $record): array
    {
        $model = $resource['model'];
        $table = (new $model)->getTable();
        $recordId = $record?->getKey();
        $codeRules = [$resource['code_required'] ? 'required' : 'nullable', 'string', 'max:100', 'alpha_dash'];
        $codeRules[] = Rule::unique($table, 'code')->ignore($recordId);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique($table, 'name')->ignore($recordId)],
            'code' => $codeRules,
        ], [], ['code' => strtolower($resource['singular']).' code']);

        if ($resource['form'] === 'nationality') {
            $request->validate([
                'code' => ['nullable', 'string', 'size:3', 'alpha:ascii'],
            ]);
            $data['code'] = $data['code'] ? strtoupper($data['code']) : null;
        }

        if ($resource['form'] === 'training-type') {
            $data['description'] = $request->validate([
                'description' => ['nullable', 'string', 'max:2000'],
            ])['description'] ?? null;
        }

        if ($resource['form'] === 'document-type') {
            $data = array_merge($data, $request->validate([
                'is_required' => ['nullable', 'boolean'],
                'allowed_extensions' => ['nullable', 'string', 'max:1000'],
                'allowed_mime_types' => ['nullable', 'string', 'max:2000'],
                'max_size_kb' => ['required', 'integer', 'min:1', 'max:102400'],
                'min_size_kb' => ['required', 'integer', 'min:1', 'max:102400'],
                'is_active' => ['nullable', 'boolean'],
                'description' => ['nullable', 'string', 'max:2000'],
            ]));
            $data['is_required'] = $request->boolean('is_required');
            $data['is_active'] = $request->boolean('is_active');
            $data['allowed_extensions'] = $this->splitList($data['allowed_extensions'] ?? null);
            $data['allowed_mime_types'] = $this->splitList($data['allowed_mime_types'] ?? null);

            if ($data['min_size_kb'] > $data['max_size_kb']) {
                throw ValidationException::withMessages([
                    'min_size_kb' => 'The minimum file size cannot exceed the maximum file size.',
                ]);
            }
        }

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function splitList(?string $value): array
    {
        return array_values(array_filter(array_map(
            fn (string $item): string => trim($item),
            explode(',', $value ?? ''),
        )));
    }
}
