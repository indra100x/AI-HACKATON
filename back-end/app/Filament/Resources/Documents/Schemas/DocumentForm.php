<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Document Name')
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $set('path', str_replace(' ', '-', strtolower($state)));
                        }
                    }),
                TextInput::make('path')
                    ->required()
                    ->maxLength(255)
                    ->label('Document Path')
                    ->helperText('Will be saved as DOCS/{path}/filename')
                    ->disabled()
                    ->dehydrated(),
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
