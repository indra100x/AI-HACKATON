<?php

namespace App\Filament\Resources\Feedback\Pages;

use App\Filament\Resources\Feedback\FeedbackResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateFeedback extends CreateRecord
{
    protected static string $resource = FeedbackResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
