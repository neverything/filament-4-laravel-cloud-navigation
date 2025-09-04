@php
    use Filament\Actions\Action;
    use Filament\Models\Contracts\HasCurrentTenantLabel;
    use Filament\Support\Facades\FilamentView;
    use Filament\Support\Icons\Heroicon;
    use Filament\View\PanelsIconAlias;
    use Filament\View\PanelsRenderHook;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Arr;
    use Illuminate\View\ComponentAttributeBag;
    use function Filament\Support\generate_icon_html;
    use function Filament\Support\prepare_inherited_attributes;

    $currentTenant = filament()->getTenant();
    $currentTenantName = filament()->getTenantName($currentTenant);

    $items = $this->getTenantMenuItems();

    $canSwitchTenants = count($tenants = array_filter(
        filament()->getUserTenants(filament()->auth()->user()),
        fn (Model $tenant): bool => ! $tenant->is($currentTenant),
    ));

    $itemsBeforeAndAfterTenantSwitcher = collect($items)
        ->groupBy(fn (Action $item): bool => $canSwitchTenants && ($item->getSort() < 0), preserveKeys: true)
        ->all();
    $itemsBeforeTenantSwitcher = $itemsBeforeAndAfterTenantSwitcher[true] ?? collect();
    $itemsAfterTenantSwitcher = $itemsBeforeAndAfterTenantSwitcher[false] ?? collect();

    $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
@endphp

{{ FilamentView::renderHook(PanelsRenderHook::TENANT_MENU_BEFORE) }}

<div class="flex items-center gap-x-2">
    <a href="{{ filament()->getUrl() }}"
       x-data="{ tooltip: false }"
       x-effect="
                    tooltip = {
                              content: @js('Current Team'),
                              placement: document.dir === 'rtl' ? 'left' : 'right',
                              theme: $store.theme,
                          }
                "
       x-tooltip.html="tooltip"
       class="text-sm font-medium inline-flex items-center gap-2 text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-lg p-2">
        <x-filament-panels::avatar.tenant
            :tenant="$currentTenant"
            loading="lazy"
            size="sm"
        />
        {{ $currentTenantName }}
    </a>

    <x-filament::dropdown placement="bottom-center" teleport max-height="80dvh">
        <x-slot name="trigger">
            <x-filament::icon-button
                color="gray"
                :icon="Heroicon::ChevronDown"
                size="sm"
                class="text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-lg p-1"
            />
        </x-slot>

        @if ($itemsBeforeTenantSwitcher->isNotEmpty())
            <x-filament::dropdown.list>
                @foreach ($itemsBeforeTenantSwitcher as $item)
                    {{ $item }}
                @endforeach
            </x-filament::dropdown.list>
        @endif

        @if ($canSwitchTenants)
            <x-filament::dropdown.list>
                @foreach ($tenants as $tenant)
                    @php
                        $tenantUrl = filament()->getUrl($tenant);
                        $tenantImage = filament()->getTenantAvatarUrl($tenant);
                    @endphp

                    <x-filament::dropdown.list.item
                        :href="$tenantUrl"
                        :image="$tenantImage"
                        tag="a"
                    >
                        {{ filament()->getTenantName($tenant) }}
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>
        @endif

        @if ($itemsAfterTenantSwitcher->isNotEmpty())
            <x-filament::dropdown.list>
                @foreach ($itemsAfterTenantSwitcher as $item)
                    {{ $item }}
                @endforeach
            </x-filament::dropdown.list>
        @endif
    </x-filament::dropdown>
</div>

{{ FilamentView::renderHook(PanelsRenderHook::TENANT_MENU_AFTER) }}
<?php
