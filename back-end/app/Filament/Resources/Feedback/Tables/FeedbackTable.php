<?php

namespace App\Filament\Resources\Feedback\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedbackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document.name')
                    ->searchable()
                    ->sortable()
                    ->label('Document'),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('User'),
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'positive' => 'success',
                        'negative' => 'danger',
                    })
                    ->label('Rating'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created At'),
                IconColumn::make('approved')
                    ->boolean()
                    ->label('Approved'),
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
