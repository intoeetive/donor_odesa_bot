<?php

namespace App\Http\Livewire\Locations;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class LocationsTable extends DataTableComponent
{

    protected $model = Location::class;

    public function columns(): array
    {
        return [
            Column::make('Name')
                ->sortable()
                ->searchable(),
            Column::make('Address'),
            Column::make('Координати', 'coords'),
            Column::make('Інструкції боту', 'bot_instructions')
        ];
    }
}
