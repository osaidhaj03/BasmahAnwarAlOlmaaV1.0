<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    إنشاء سند قبض
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    افتح نموذج سند قبض جديد وسجّل الدفعة بالدينار الأردني.
                </p>
            </div>

            <x-filament::button
                :href="$this->getCreatePaymentUrl()"
                tag="a"
                icon="heroicon-m-plus-circle"
            >
                سند قبض جديد
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
