<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('contact_id')
                    ->relationship('contact', 'name')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                TextInput::make('channel')
                    ->required()
                    ->default('whatsapp'),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('last_message_at'),
                DateTimePicker::make('escalated_at'),
                TextInput::make('escalated_reason'),
            ]);
    }
}
