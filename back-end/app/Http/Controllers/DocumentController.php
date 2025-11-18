<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Upload a document
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,doc,jpg,jpeg,png,mp4',
        ]);

        $user = $request->user();
        $file = $request->file('file');

        // Get file extension
        $extension = strtolower($file->getClientOriginalExtension());

        // Store file in DOCS folder
        $path = $file->store('DOCS', 'public');

        // Create document record without decision
        $document = Document::create([
            'user_id' => $user->id,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'extension' => $extension,
            // decision is not set, will be filled later by admin
        ]);

        // Process the document automatically
        $processingResult = DocumentProcessingService::processDocument($document);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded and processing initiated',
            'document' => $document,
            'processing' => $processingResult,
        ], 201);
    }

    /**
     * Get all documents for the authenticated user
     */
    public function getUserDocuments(Request $request)
    {
        $user = $request->user();
        $documents = $user->documents()->paginate(10);

        return response()->json([
            'success' => true,
            'documents' => $documents,
        ]);
    }

    /**
     * Get a specific document
     */
    public function getDocument(Request $request, Document $document)
    {
        // Check if user owns this document
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

    /**
     * Download a document
     */
    public function downloadDocument(Request $request, Document $document)
    {
        // Check if user owns this document
        if ($document->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if file exists
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

    /**
     * Delete a document
     */
    public function deleteDocument(Request $request, Document $document)
    {
        // Check if user owns this document
        if ($document->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        // Delete document record
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully',
        ]);
    }
}
