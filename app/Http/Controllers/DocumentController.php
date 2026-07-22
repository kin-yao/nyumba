<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Lease;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // ── Landlord: upload a document to a tenant's current lease ────────────
    public function store(Request $request, Tenant $tenant)
    {
        if ($tenant->account_id !== auth()->user()->account_id) {
            abort(403);
        }

        $lease = $tenant->activeLease;
        if (!$lease) {
            return back()->with('error', 'This tenant has no active lease to attach a document to.');
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'file'  => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'], // 10MB
        ]);

        $path = $request->file('file')->store('lease-documents', 'r2');

        Document::create([
            'account_id'        => $tenant->account_id,
            'lease_id'          => $lease->id,
            'uploaded_by'       => auth()->id(),
            'label'             => $validated['label'],
            'path'              => $path,
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'file_size'         => $request->file('file')->getSize(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    // ── Landlord: delete a document ─────────────────────────────────────────
    public function destroy(Document $document)
    {
        if ($document->account_id !== auth()->user()->account_id) {
            abort(403);
        }

        if (Storage::disk('r2')->exists($document->path)) {
            Storage::disk('r2')->delete($document->path);
        }

        $document->delete();

        return back()->with('success', 'Document removed.');
    }

    // ── Download/stream — reachable by either the landlord (own account)
    // or the tenant portal (own lease only), depending on which route hit this.
    public function download(Document $document)
    {
        $authorized = false;

        if (auth()->check() && $document->account_id === auth()->user()->account_id) {
            $authorized = true;
        }

        $portalTenantId = session('portal_tenant_id');
        if ($portalTenantId) {
            $tenant = Tenant::find($portalTenantId);
            if ($tenant && $tenant->activeLease && $tenant->activeLease->id === $document->lease_id) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            abort(403);
        }

        if (!Storage::disk('r2')->exists($document->path)) {
            abort(404);
        }

        $contents = Storage::disk('r2')->get($document->path);
        $mime     = Storage::disk('r2')->mimeType($document->path) ?? 'application/octet-stream';

        return response($contents, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . $document->original_filename . '"');
    }
}