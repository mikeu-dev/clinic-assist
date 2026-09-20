<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('faq_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('question')
                    ->label('Pertanyaan Pasien (FAQ)')
                    ->placeholder('Contoh: Kapan jam buka klinik?')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('answer')
                    ->label('Jawaban Resmi')
                    ->placeholder('Jawaban informatif yang akan dikirim via WhatsApp...')
                    ->rows(5)
                    ->required()
                    ->columnSpanFull(),
                TagsInput::make('keywords')
                    ->label('Kata Kunci (Keywords)')
                    ->placeholder('Ketik kata kunci lalu Enter (misal: jam buka, jadwal, operasional)')
                    ->helperText('Kata kunci digunakan bot untuk mencocokkan pesan pasien.')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktifkan FAQ ini')
                    ->default(true)
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
