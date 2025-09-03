<?php

namespace App\Filament\Resources\Catalogs\Resources\Links;

use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Resources\Catalogs\Resources\Links\Pages\CreateLink;
use App\Filament\Resources\Catalogs\Resources\Links\Pages\EditLink;
use App\Filament\Resources\Catalogs\Resources\Links\Pages\ManageLinks;
use App\Filament\Resources\Catalogs\Resources\Links\Schemas\LinkForm;
use App\Filament\Resources\Catalogs\Resources\Links\Tables\LinksTable;
use App\Models\Link;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LinkResource extends Resource
{
    protected static ?string $model = Link::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = CatalogResource::class;

    protected static bool $isScopedToTenant = false;

    protected static ?string $recordTitleAttribute = 'url';

    public static function form(Schema $schema): Schema
    {
        return LinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LinksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLinks::route('/'),
        ];
    }
}
