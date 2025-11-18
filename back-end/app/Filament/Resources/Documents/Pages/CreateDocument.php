<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        // Get the dynamic DOCS folder path
        $docsPath = base_path('storage/app/public/DOCS');

        // Ensure DOCS folder exists
        if (!is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
        }

        // Construct the full file system path dynamically
        $data['path'] = $docsPath . DIRECTORY_SEPARATOR . $data['path'];

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
