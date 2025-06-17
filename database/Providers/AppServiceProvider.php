<?php

namespace database\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('notify', function () {
            return <<<'HTML'
        @if(session('toast'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Toastify({
                    text: "<?php echo session('toast.message') ?>",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "<?php
                        echo match(session('toast.type')) {
                            'success' => '#4CAF50',
                            'error' => '#F44336',
                            'warning' => '#FF9800',
                            default => '#2196F3'
                        }
                    ?>",
                }).showToast();
            });
        </script>
        @endif
        HTML;
        });
    }
}
