<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submit Blood Request') }}
        </h2>
    </x-slot>

    <div>
        <div class="mx-auto py-10 sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('blood-requests.store') }}">
                @csrf

                <div class="inline-block">
                    <x-jet-label for="qty" value="{{ __('Число донорів') }}" />
                    <x-jet-input id="qty" class="block mt-1 w-half" type="number" step="1" name="qty" :value="old('qty')" required />
                </div>

                <div class="inline-block">
                    <x-jet-label for="blood_type_id" value="{{ __('Група крові') }}" />
                    <select name="type" id="type" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-half" required>
                        <option value=""></option>
                        @foreach ($bloodTypes as $id =>$label)
                            <option value="{{$id}}">{{$label}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="inline-block">
                    <x-jet-label for="location_id" value="{{ __('Лікарня') }}" />
                    <select name="location_id" id="location_id" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-half" required>
                        <option value=""></option>
                        @foreach ($locations as $location)
                            <option value="{{$location->id}}">{{$location->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="inline-block">
                    <x-jet-button>
                        {{ __('Надіслати запит') }}
                    </x-jet-button>
                </div>
            </form>


        </div>
    </div>
</x-app-layout>
