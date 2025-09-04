<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Tenancy\RegisterTeam;
use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Resources\Catalogs\Resources\Links\LinkResource;
use App\Livewire\CustomTopbar;
use App\Models\Catalog;
use App\Models\Team;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use function Filament\Support\original_request;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->tenant(Team::class)
            ->topNavigation()
            ->topbarLivewireComponent(CustomTopbar::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->tenantRegistration(RegisterTeam::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $tenant = Filament::getTenant();
                $catalogs = $tenant->catalogs;
                $builder->groups(
                    [
                        NavigationGroup::make('Catalogs')
                            ->items(
                                $catalogs
                                    ->map(
                                        fn (Catalog $catalog): NavigationItem => NavigationItem::make($catalog->name)
                                            ->icon(Heroicon::DocumentCheck)
                                            ->url(CatalogResource::getUrl())
                                            ->isActiveWhen(fn (): bool => original_request()->route('catalog') == $catalog->id)
                                    )->toArray()
                            ),
                        ...$catalogs
                            ->filter(fn (Catalog $catalog): bool => original_request()->route('catalog') == $catalog->id)
                            ->map(fn (Catalog $catalog): NavigationGroup => NavigationGroup::make($catalog->name)
                                ->items([
                                    NavigationItem::make(LinkResource::getNavigationLabel())
                                        ->icon(fn (): string|BackedEnum|Htmlable|null => LinkResource::getNavigationIcon())
                                        ->url(
                                            LinkResource::getUrl(
                                                name: 'index',
                                                parameters: ['tenant' => $catalog->team_id, 'catalog' => $catalog->id]
                                            ))
                                        ->isActiveWhen(fn (): bool => original_request()->routeIs('filament.admin.resources.catalogs.links.*')
                                                && original_request()->route('catalog') == $catalog->id
                                        )
                                        ->badge(fn () => original_request()->routeIs('filament.admin.resources.catalogs.*')
                                            && original_request()->route('catalog') == $catalog->id
                                                ? Cache::flexible(
                                                    key: 'links_count:'.$catalog->id,
                                                    ttl: [10, 60],
                                                    callback: fn () => $catalog->loadCount('links')->links_count
                                                )
                                                : null
                                        ),
                                ])
                                ->collapsed()
                            )->toArray(),
                    ],
                );

                return $builder;
            })
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
