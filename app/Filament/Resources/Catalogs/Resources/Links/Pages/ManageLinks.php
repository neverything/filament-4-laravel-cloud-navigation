<?php

namespace App\Filament\Resources\Catalogs\Resources\Links\Pages;

use App\Filament\Resources\Catalogs\Resources\Links\LinkResource;
use App\Models\Link;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageLinks extends ManageRecords
{
    protected static string $resource = LinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createLinks')
                ->label('Add Links')
                ->icon('heroicon-o-plus')
                ->schema([
                    Textarea::make('links')
                        ->columnSpanFull()
                        ->helperText('Enter one url per line. Duplicates will automatically be skipped.')
                        ->required(),
                ])
                ->mutateDataUsing(function ($data): array {
                    $links = collect(explode("\n", $data['links'] ?? ''))
                        ->map(fn ($link): string => trim($link))
                        ->map(fn ($link): string => rtrim($link, ','))
                        ->map(fn ($link): string => trim($link))
                        ->map(fn ($link): string => rtrim($link, '/'))
                        ->map(fn ($link): string => trim($link))
                        ->filter(fn ($link): bool => $link !== '' && $link !== '0')
                        ->filter(fn ($link) => filter_var($link, FILTER_VALIDATE_URL))
                        ->filter(fn ($link): bool => ! Link::where('url', $link)->where('catalog_id', $this->getParentRecord()->id)->exists())
                        ->unique()
                        ->values();

                    return ['links' => $links->toArray()];
                })
                ->action(function (Action $action, array $data): void {
                    if (empty($data['links'])) {
                        Notification::make()
                            ->warning()
                            ->title('No new links provided.')
                            ->body('Please provide at least one link.')
                            ->send();
                        $action->cancel();
                    }

                    $this->getParentRecord()
                        ->links()
                        ->createMany(
                            array_map(
                                fn ($link): array => [
                                    'url' => $link,
                                    'sourceable_type' => User::class,
                                    'sourceable_id' => auth()->id(),
                                ],
                                $data['links'] ?? []
                            )
                        );
                })
                ->after(function (): void {
                    Notification::make()
                        ->success()
                        ->title('Links added successfully.')
                        ->send();
                }),
        ];
    }
}
