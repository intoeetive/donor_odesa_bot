<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Donor Responses') }}
        </h2>
    </x-slot>

    <div>
        <div class="mx-auto py-10 sm:px-6 lg:px-8">

            @livewire('responses-table')

        </div>
    </div>
</x-app-layout>
