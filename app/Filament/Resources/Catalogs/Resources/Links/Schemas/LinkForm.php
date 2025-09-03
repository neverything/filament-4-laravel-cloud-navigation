<?php

namespace App\Filament\Resources\Catalogs\Resources\Links\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('url')
                    ->required(),
            ]);
    }
}
