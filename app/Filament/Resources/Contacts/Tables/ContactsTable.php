<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                TextColumn::make('phone_number')
                    ->label('No. WhatsApp')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),
                TextColumn::make('profile_name')
                    ->label('Nama Profil WA')
                    ->searchable()
                    ->color('gray'),
                TextColumn::make('conversations_count')
                    ->label('Total Sesi Chat')
                    ->counts('conversations')
                    ->badge()
                    ->color('info'),
                TextColumn::make('last_interaction_at')
                    ->label('Interaksi Terakhir')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('last_interaction_at', 'desc')
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
