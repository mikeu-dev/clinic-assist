<?php

namespace App\Filament\Resources\ClinicSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClinicSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Nama Pengaturan')
                    ->required(),
                TextInput::make('key')
                    ->label('Kunci (Key)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (?object $record): bool => $record !== null),
                Select::make('group')
                    ->label('Kategori')
                    ->options([
                        'general' => 'Umum Klinik',
                        'contact' => 'Kontak & Lokasi',
                        'bot' => 'Chatbot WhatsApp',
                    ])
                    ->required()
                    ->default('general'),
                Textarea::make('value')
                    ->label('Isi / Nilai')
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }
}
