<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentProcessingService
{
    public static function processDocument(Document $document)
    {
        try {
            $filePath = Storage::disk('public')->path($document->path);

            $extension = strtolower($document->extension);

            switch ($extension) {
                case 'pdf':
                    return self::processPdf($filePath, $document);
                case 'docx':
                case 'doc':
                    return self::processWord($filePath, $document);
                case 'jpg':
                case 'jpeg':
                case 'png':
                    return self::processImage($filePath, $document);
                case 'mp4':
                    return self::processVideo($filePath, $document);
                default:
                    return ['status' => 'unsupported', 'message' => 'File type not supported for processing'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private static function processPdf($filePath, Document $document)
    {
        return [
            'status' => 'processed',
            'type' => 'pdf',
            'message' => 'PDF processing initiated',
            'file' => $filePath,
        ];
    }

    private static function processWord($filePath, Document $document)
    {
        return [
            'status' => 'processed',
            'type' => 'word',
            'message' => 'Word document processing initiated',
            'file' => $filePath,
        ];
    }

    private static function processImage($filePath, Document $document)
    {
        return [
            'status' => 'processed',
            'type' => 'image',
            'message' => 'Image processing initiated',
            'file' => $filePath,
        ];
    }

    private static function processVideo($filePath, Document $document)
    {
        return [
            'status' => 'processed',
            'type' => 'video',
            'message' => 'Video processing initiated',
            'file' => $filePath,
        ];
    }
}
