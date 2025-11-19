<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Feedback;
use App\Services\DocumentProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,mp4|max:50000',
            'decision' => 'sometimes|string',
            'user_feedback' => 'sometimes|string',
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

        // If frontend provided a decision and user feedback, save feedback and forward to FastAPI
        $decision = $request->input('decision'); // 'FAKE' or 'REAL'
        $userFeedback = $request->input('user_feedback'); // 'FAKE' or 'REAL'

        $feedbackRecord = null;

        if ($userFeedback) {
            // map FAKE/REAL -> negative/positive for DB
            $rating = strtolower($userFeedback) === 'real' ? 'positive' : 'negative';

            $feedbackRecord = Feedback::create([
                'user_id' => $user->id,
                'document_id' => $document->id,
                'rating' => $rating,
                'approved' => false,
            ]);

            // forward feedback to FastAPI /feedback endpoint (attach file)
            try {
                $fastapiUrl = env('FASTAPI_URL', 'http://127.0.0.1:8001');
                $filePath = Storage::disk('public')->path($document->path);

                if (file_exists($filePath)) {
                    Http::attach('file', fopen($filePath, 'r'), $document->name)
                        ->post(rtrim($fastapiUrl, '/') . '/feedback', [
                            'user_feedback' => strtoupper($userFeedback),
                        ]);
                }
            } catch (\Exception $e) {
                // Do not fail the request if forwarding feedback fails; could log instead
            }
        }

        // Keep existing processing as fallback
        $processingResult = DocumentProcessingService::processDocument($document);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded and processing initiated',
            'document' => $document,
            'processing' => $processingResult,
            'feedback' => $feedbackRecord,
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

    /**
     * Store the uploaded file, create Document and forward file to FastAPI for prediction.
     * Returns document record + prediction to frontend so the file is uploaded only once.
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,mp4|max:50000',
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

        // Forward the stored file to FastAPI predict endpoint
        $fastapiUrl = env('FASTAPI_URL', 'http://127.0.0.1:8001');
        $filePath = Storage::disk('public')->path($document->path);

        $prediction = null;

        try {
            if (file_exists($filePath)) {
                $endpoint = in_array($extension, ['mp4', 'mov', 'avi']) ? '/predict/video' : '/predict/image';

                $response = Http::attach('file', fopen($filePath, 'r'), $document->name)
                    ->post(rtrim($fastapiUrl, '/') . $endpoint);

                if ($response->ok()) {
                    $prediction = $response->json();
                } else {
                    $prediction = ['success' => false, 'message' => $response->body()];
                }
            }
        } catch (\Exception $e) {
            $prediction = ['success' => false, 'message' => $e->getMessage()];
        }

        return response()->json([
            'success' => true,
            'document' => $document,
            'prediction' => $prediction,
        ], 200);
    }
}
