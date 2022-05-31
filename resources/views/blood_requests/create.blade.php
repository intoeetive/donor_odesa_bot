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

                <div class="block">
                    <x-jet-label for="qty" value="{{ __('Number of donors') }}" />
                    <x-jet-input id="qty" class="block mt-1 w-full" type="number" step="1" name="qty" :value="old('qty')" required />
                </div>

                <div class="block">
                    <x-jet-label for="blood_type_id" value="{{ __('Blood Type') }}" />
                    <select name="type" id="type" class="form-control" required>
                        <option value=""></option>
                        @foreach ($bloodTypes as $id =>$label)
                            <option value="{{$id}}">{{$label}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="block">
                    <x-jet-label for="location_id" value="{{ __('Location') }}" />
                    <select name="location_id" id="location_id" class="form-control" required>
                        <option value=""></option>
                        @foreach ($locations as $location)
                            <option value="{{$location->id}}">{{$location->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-jet-button>
                        {{ __('Submit Request') }}
                    </x-jet-button>
                </div>
            </form>


        </div>
    </div>
</x-app-layout>
