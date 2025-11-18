<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Feedback;
use Illuminate\Http\Request;

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

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'feedback' => $feedback,
        ], 201);
    }
}
