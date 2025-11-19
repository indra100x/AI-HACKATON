<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class FeedbackController extends Controller
{
    public function submitFeedback(Request $request, Document $document)
    {
        if ($document->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'rating' => 'required|in:positive,negative',
        ]);

        $existingFeedback = Feedback::where('user_id', $request->user()->id)
            ->where('document_id', $document->id)
            ->first();

        if ($existingFeedback) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback already submitted for this document',
            ], 422);
        }

        $feedback = Feedback::create([
            'user_id' => $request->user()->id,
            'document_id' => $document->id,
            'rating' => $request->input('rating'),
            'approved' => false,
        ]);

        // Forward feedback to FastAPI feedback endpoint (attach stored file)
        try {
            $fastapiUrl = env('FASTAPI_URL', 'http://127.0.0.1:8001');
            $filePath = Storage::disk('public')->path($document->path);

            if (file_exists($filePath)) {
                // Map rating back to FASTAPI expected user_feedback
                $userFeedback = $request->input('rating') === 'positive' ? 'REAL' : 'FAKE';

                Http::attach('file', fopen($filePath, 'r'), $document->name)
                    ->post(rtrim($fastapiUrl, '/') . '/feedback', [
                        'user_feedback' => $userFeedback,
                    ]);
            }
        } catch (\Exception $e) {
            // swallow errors; could log
        }

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'feedback' => $feedback,
        ], 201);
    }
}
