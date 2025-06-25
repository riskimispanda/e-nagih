<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson);

        // Filter menu based on user role
        View::composer('*', function ($view) use ($verticalMenuData) {
            if (Auth::check()) {
                $userRole = Auth::user()->roles->name; // Assuming roles relationship exists
                $filteredMenu = $this->filterMenuByRole($verticalMenuData->menu, $userRole);
                $verticalMenuData->menu = $filteredMenu;
            }
            
            $view->with('menuData', [$verticalMenuData]);
        });
    }

    private function filterMenuByRole($menuItems, $userRole)
    {
        return array_filter($menuItems, function ($item) use ($userRole) {
            // Check if menu item has roles defined
            if (isset($item->roles)) {
                // Keep item if user's role is in the allowed roles
                $hasAccess = in_array($userRole, $item->roles);
                
                // If item has submenu, filter it too
                if ($hasAccess && isset($item->submenu)) {
                    $item->submenu = $this->filterMenuByRole($item->submenu, $userRole);
                }
                
                return $hasAccess;
            }
            
            return true; // Keep items without roles restriction
        });
    }
}
