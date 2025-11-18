<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,pdf,doc,docx,mp4|max:50000',
        ]);

        $user = $request->user();
        $file = $request->file('file');

        $extension = strtolower($file->getClientOriginalExtension());

        $path = $file->store('DOCS', 'public');

        $document = Document::create([
            'user_id' => $user->id,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'extension' => $extension,
        ]);

        $processingResult = DocumentProcessingService::processDocument($document);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded and processing initiated',
            'document' => $document,
            'processing' => $processingResult,
        ], 201);
    }

    public function getUserDocuments(Request $request)
    {
        $user = $request->user();
        $documents = $user->documents()->paginate(10);

        return response()->json([
            'success' => true,
            'documents' => $documents,
        ]);
    }

    public function getDocument(Request $request, Document $document)
    {
        if ($document->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'document' => $document->load('feedbacks'),
        ]);
    }

    public function downloadDocument(Request $request, Document $document)
    {
        if ($document->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!Storage::disk('public')->exists($document->path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return response()->download(
            Storage::disk('public')->path($document->path),
            $document->name
        );
    }

    public function deleteDocument(Request $request, Document $document)
    {
        if ($document->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully',
        ]);
    }

    public function getDecision(Request $request, Document $document)
    {
        if ($document->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'document_id' => $document->id,
            'decision' => $document->decision,
        ]);
    }
}
