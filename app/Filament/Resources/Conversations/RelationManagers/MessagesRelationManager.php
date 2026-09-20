<?php

namespace App\Filament\Resources\Conversations\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('wa_message_id'),
                TextInput::make('direction')
                    ->required(),
                TextInput::make('message_type')
                    ->required()
                    ->default('text'),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('raw_payload')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('received'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('direction')
                    ->label('Arah')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'incoming' => 'info',
                        'outgoing' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'incoming' => '📥 Pasien',
                        'outgoing' => '🤖 Bot/Admin',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('body')
                    ->label('Isi Pesan')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('message_type')
                    ->label('Tipe')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'received' => 'info',
                        'sent' => 'warning',
                        'delivered' => 'success',
                        'read' => 'primary',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'asc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
