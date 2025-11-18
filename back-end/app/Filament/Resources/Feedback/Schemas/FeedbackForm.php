<?php

namespace App\Filament\Resources\Feedback\Schemas;

use App\Models\Document;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class FeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('document_id')
                    ->label('Document')
                    ->required()
                    ->options(fn () => Document::pluck('name', 'id'))
                    ->searchable(),
                Select::make('rating')
                    ->options(['positive' => 'Positive', 'negative' => 'Negative'])
                    ->required(),
                Checkbox::make('approved')
                    ->label('Approved')
                    ->default(false),
            ]);
    }
}
