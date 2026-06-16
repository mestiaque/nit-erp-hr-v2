<?php
namespace ME\Hr;

use Illuminate\Support\ServiceProvider;

class HrServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (file_exists(__DIR__ . '/routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }

        if (file_exists(__DIR__ . '/routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        }

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'hr');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'hr');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->publishes([
            __DIR__ . '/Config' => config_path('hr'),
        ], 'hr-config');

        // Merge hr-permission into main permission config
        $hrPermissions = config('hr-permission');
        if ($hrPermissions && is_array($hrPermissions)) {
            $mainPermissions = config('permission', []);
            // If main permission is using ['modules' => ...] structure, merge into modules
            if (isset($mainPermissions['modules']) && is_array($mainPermissions['modules'])) {
                $mainPermissions['modules']['HR AND COMPLIANCE'] = $hrPermissions['HR AND COMPLIANCE'] ?? [];
                config(['permission' => $mainPermissions]);
            } else {
                // Flat merge (fallback)
                config(['permission' => array_merge($mainPermissions, $hrPermissions)]);
            }
        }
    }

    public function register()
    {
        if (file_exists(__DIR__ . '/Config/config.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'hr');
        }

        if (file_exists(__DIR__ . '/Config/sidebar.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/sidebar.php', 'hr-sidebar');
        }

        if (file_exists(__DIR__ . '/Config/permission.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/permission.php', 'hr-permission');
        }
    }
}
