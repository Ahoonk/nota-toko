<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DocumentTemplate;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $templates = DocumentTemplate::query()
            ->with('company')
            ->latest('id')
            ->paginate(10);

        return view('templates.index', [
            'templates' => $templates,
            'documentTypes' => config('nota_toko.document_types'),
        ]);
    }

    public function create(): View
    {
        return view('templates.form', [
            'template' => null,
            'companies' => Company::query()->orderBy('name')->pluck('name', 'id'),
            'documentTypes' => config('nota_toko.document_types'),
        ]);
    }

    public function store(Request $request, AuditLogService $auditLogService): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'document_type' => ['required', 'string', 'in:'.implode(',', array_keys(config('nota_toko.document_types')))],
            'name' => ['required', 'string', 'max:255'],
            'template_file' => ['required', 'file', 'mimes:pdf'],
            'notes' => ['nullable', 'string'],
        ]);

        $path = $request->file('template_file')->store('templates', 'public');
        $template = DocumentTemplate::updateOrCreate(
            [
                'company_id' => $data['company_id'],
                'document_type' => $data['document_type'],
            ],
            [
                'name' => $data['name'],
                'template_path' => $path,
                'is_active' => true,
                'notes' => $data['notes'] ?? null,
            ]
        );

        $auditLogService->record($request, $template, 'template_saved', [], $template->toArray());

        return redirect()->route('document-templates.index')->with('status', 'Template berhasil disimpan. Template siap digunakan.');
    }

    public function edit(DocumentTemplate $documentTemplate): View
    {
        return view('templates.form', [
            'template' => $documentTemplate->load('company'),
            'companies' => Company::query()->orderBy('name')->pluck('name', 'id'),
            'documentTypes' => config('nota_toko.document_types'),
        ]);
    }

    public function update(Request $request, DocumentTemplate $documentTemplate, AuditLogService $auditLogService): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'document_type' => ['required', 'string', 'in:'.implode(',', array_keys(config('nota_toko.document_types')))],
            'name' => ['required', 'string', 'max:255'],
            'template_file' => ['nullable', 'file', 'mimes:pdf'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('template_file')) {
            if ($documentTemplate->template_path) {
                Storage::disk('public')->delete($documentTemplate->template_path);
            }

            $data['template_path'] = $request->file('template_file')->store('templates', 'public');
        }

        $documentTemplate->update([
            'company_id' => $data['company_id'],
            'document_type' => $data['document_type'],
            'name' => $data['name'],
            'template_path' => $data['template_path'] ?? $documentTemplate->template_path,
            'is_active' => $request->boolean('is_active'),
            'notes' => $data['notes'] ?? null,
        ]);

        $auditLogService->record($request, $documentTemplate, 'template_updated', [], $documentTemplate->fresh()->toArray());

        return redirect()->route('document-templates.index')->with('status', 'Template berhasil diperbarui.');
    }

    public function destroy(Request $request, DocumentTemplate $documentTemplate, AuditLogService $auditLogService): RedirectResponse
    {
        $auditLogService->record($request, $documentTemplate, 'template_deleted', $documentTemplate->toArray(), []);
        $documentTemplate->delete();

        return redirect()->route('document-templates.index')->with('status', 'Template berhasil dihapus.');
    }
}
