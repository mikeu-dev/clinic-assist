<?php

namespace App\Filament\Resources\ClinicSettings\Pages;

use App\Filament\Resources\ClinicSettings\ClinicSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClinicSettings extends ListRecords
{
    protected static string $resource = ClinicSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
