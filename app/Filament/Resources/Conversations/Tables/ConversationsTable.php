<?php

namespace App\Filament\Resources\Conversations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contact.name')
                    ->label('Nama Kontak / Pasien')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                TextColumn::make('contact.phone_number')
                    ->label('No. WhatsApp')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'escalated' => 'danger',
                        'resolved', 'closed' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif (Bot)',
                        'escalated' => 'Eskalasi ke Admin',
                        'resolved' => 'Selesai',
                        'closed' => 'Ditutup',
                        default => ucfirst($state),
                    }),
                TextColumn::make('messages_count')
                    ->label('Jumlah Pesan')
                    ->counts('messages')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('last_message_at')
                    ->label('Pesan Terakhir')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('escalated_reason')
                    ->label('Alasan Eskalasi')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Percakapan')
                    ->options([
                        'active' => 'Aktif (Bot)',
                        'escalated' => 'Eskalasi ke Admin',
                        'resolved' => 'Selesai',
                        'closed' => 'Ditutup',
                    ]),
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
