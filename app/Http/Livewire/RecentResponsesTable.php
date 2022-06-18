<?php

namespace App\Http\Livewire;

use App\Models\BloodType;
use App\Models\Donor;
use App\Models\Location;
use App\Models\BloodRequest;
use App\Models\DonorBloodRequestResponse;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\NumberFilter;
use Illuminate\Support\Facades\Auth;

class RecentResponsesTable extends ResponsesTable
{
    public function configure(): void
    {
        parent::configure();
        $this->setFiltersDisabled();
        $this->setSearchDisabled();
        $this->setPerPage(50);
    }

    public function builder(): Builder
    {
        $locations = Auth::user()->locations()->pluck('id')->toArray();
        return DonorBloodRequestResponse::query()
            ->whereIn('location_id', $locations)
            ->whereNull('no_donorship')
            ->whereNull('donorship_date');
    }

    public function bulkActions(): array
    {
        return parent::bulkActions();
    }


}
