<?php

namespace App\Filament\Resources\Catalogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
