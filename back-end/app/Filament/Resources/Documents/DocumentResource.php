<?php

namespace App\Filament\Resources\Documents;

use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Filament\Resources\Documents\Schemas\DocumentForm;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use App\Models\Document;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

/**
 * @mixin \Filament\Resources\Resource
 */
class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * @param Schema $schema
     * @return Schema
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // This will be configured by DocumentForm if using the Schema pattern
                // For now, return empty form to satisfy the type signature
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }
}
