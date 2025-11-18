<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentForm
{
    /**
     * @param Schema $schema
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Document Name'),
                TextInput::make('path')
                    ->required()
                    ->maxLength(255)
                    ->label('Document Path'),
                Select::make('decision')
                    ->options([
                        'fake' => 'Fake',
                        'real' => 'Real',
                    ])
                    ->required()
                    ->label('Decision'),
                Select::make('extension')
                    ->options([
                        'pdf' => 'PDF',
                        'docx' => 'DOCX',
                        'jpg' => 'JPG',
                        'jpeg' => 'JPEG',
                        'png' => 'PNG',
                        'mp4' => 'MP4',
                    ])
                    ->required()
                    ->label('Extension'),
            ]);
    }
}
