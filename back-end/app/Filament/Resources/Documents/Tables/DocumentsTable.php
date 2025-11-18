<?php

namespace App\Filament\Resources\Documents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),
                TextColumn::make('path')
                    ->searchable()
                    ->label('Path'),
                TextColumn::make('decision')
                    ->label('Decision'),
                TextColumn::make('extension')
                    ->label('Extension'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
