<?php
namespace ME\Hr;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use ME\Hr\Http\Middleware\HrMachineTokenMiddleware;

class HrServiceProvider extends ServiceProvider
{
    public function boot(Router $router)
    {
        $router->aliasMiddleware('hr.machine', HrMachineTokenMiddleware::class);

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

        $this->registerApprovalModules();
    }

    /** Leave and salary advance approvals go through the host app's central Approvals (page + mail). */
    private function registerApprovalModules(): void
    {
        if (! interface_exists(\App\Contracts\ApprovalHandlerInterface::class)) {
            return;
        }

        config(['approval.modules' => array_merge([
            \ME\Hr\Services\HrApprovals::LEAVE => \ME\Hr\Approvals\LeaveApprovalHandler::class,
            \ME\Hr\Services\HrApprovals::ADVANCE => \ME\Hr\Approvals\AdvanceApplicationApprovalHandler::class,
        ], config('approval.modules', []))]);

        config(['mail_notifications.actions' => array_merge([
            \ME\Hr\Services\HrApprovals::LEAVE => ['title' => 'Approval: HR Leave', 'group' => 'Approval', 'approval' => true],
            \ME\Hr\Services\HrApprovals::ADVANCE => ['title' => 'Approval: Salary Advance (HR & Accounts)', 'group' => 'Approval', 'approval' => true],
        ], config('mail_notifications.actions', []))]);
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

        if (file_exists(__DIR__ . '/Config/checklist.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/checklist.php', 'checklist');
        }
    }
}
