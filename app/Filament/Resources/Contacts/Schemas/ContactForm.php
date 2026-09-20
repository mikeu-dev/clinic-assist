<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone_number')
                    ->tel()
                    ->required(),
                TextInput::make('name'),
                TextInput::make('profile_name'),
                DateTimePicker::make('last_interaction_at'),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
