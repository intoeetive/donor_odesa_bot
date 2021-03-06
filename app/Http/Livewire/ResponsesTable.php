<?php

namespace App\Http\Livewire;

use App\Models\BloodType;
use App\Models\Donor;
use App\Models\Location;
use App\Models\BloodRequest;
use App\Models\DonorBloodRequestResponse;
use App\Models\DonorTelegramChat;

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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ResponsesTable extends DataTableComponent
{
    public function configure(): void
    {
        //$this->setDebugEnabled();

        $this->setPrimaryKey('id')
            //->setReorderEnabled()
            ->setAdditionalSelects([
                'donor_blood_request_responses.id as id',
                'donor_blood_request_responses.blood_request_id as blood_request_id',
                'blood_requests.blood_type_id as blood_type_id',
                'blood_requests.location_id as location_id',
                'blood_requests.created_at AS bloodRequest_created_at',
                'blood_requests.location_id AS location_name'])
            ->setEagerLoadAllRelationsEnabled()
            ->setSingleSortingDisabled()
            ->setDefaultSort('confirmation_date', 'desc')
            ->setSortingPillsEnabled()
            ->setHideReorderColumnUnlessReorderingEnabled()
            ->setFilterLayoutSlideDown()
            ->setRememberColumnSelectionDisabled()
            ->setColumnSelectDisabled()
            ->setSecondaryHeaderTrAttributes(function($rows) {
                return ['class' => 'bg-gray-100'];
            })
            ->setSecondaryHeaderTdAttributes(function(Column $column, $rows) {
                if ($column->isField('id')) {
                    return ['class' => 'text-red-500'];
                }

                return ['default' => true];
            })
            ->setFooterTrAttributes(function($rows) {
                return ['class' => 'bg-gray-100'];
            })
            ->setFooterTdAttributes(function(Column $column, $rows) {
                if ($column->isField('name')) {
                    return ['class' => 'text-green-500'];
                }

                return ['default' => true];
            })
            ->setUseHeaderAsFooterEnabled()
            ->setPerPage(25)
            ->setHideBulkActionsWhenEmptyEnabled();
    }

    public function filters(): array
    {
        //$locations = Location::pluck('name', 'id')->all();
        $locations = Auth::user()->locations()->pluck('name', 'id')->toArray();
        $requests = [];
        $requestsQuery = BloodRequest::orderByDesc('created_at')->whereIn('location_id', array_keys($locations))->limit(5)->get()->pluck('created_at', 'id');
        foreach ($requestsQuery as $id => $value) {
            $requests[$id] = Carbon::parse($value)->locale('uk')->isoFormat('LL LT');
        }
        return [
            SelectFilter::make('??????????', 'blood_request_id')
                ->options(['' => '?????? ????????????'] + $requests)
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('blood_request_id', $value);
                }),
            SelectFilter::make('??????????????')
                ->options(['' => '?????? ??????????'] + $locations)
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('location_id', $value);
                }),
            SelectFilter::make('?????????? ??????????')
                ->options(['' => '?????? ??????????'] + BloodType::BLOOD_TYPES)
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('blood_type_id', $value);
                }),
        ];
    }

    public function columns(): array
    {
        return [
            LinkColumn::make('????????', 'bloodRequest.created_at')
                ->title(fn($row) => Carbon::parse($row->bloodRequest_created_at)->locale('uk')->isoFormat('LL LT'))
                ->location(fn($row) => '/request-responses?table[filters][blood_request_id]=' . $row->blood_request_id),
            /*Column::make('??????????', 'location.name')
                ->sortable()
                ->searchable(),*/
            Column::make(__('ui.blood_type'), 'bloodRequest.blood_type_id')
                ->collapseOnTablet()
                ->sortable()
                ->searchable()
                ->format(fn($value, $row, Column $column) => !empty($value) ? BloodType::BLOOD_TYPES[$value] : '?'),
            Column::make(__('ui.donor_name'), 'donor.name')
                ->collapseOnTablet()
                ->sortable()
                ->searchable(),
            Column::make(__('ui.phone'), 'donor.phone')
                ->collapseOnTablet()
                ->searchable(),
            BooleanColumn::make(__('ui.response'), 'no_response_contras')->sortable()->searchable(),
            Column::make(__('ui.confirmation_date'), 'confirmation_date')
                ->format(
                    fn($value, $row, Column $column) =>!empty($value) ? Carbon::parse($value)->locale('uk')->isoFormat('LL LT') : ''
                ),
            Column::make("???????? ??????????????????", 'donorship_date')
                ->format(
                    fn($value, $row, Column $column) => !empty($value) ? Carbon::parse($value)->locale('uk')->isoFormat('LL LT') : ''
                ),
        ];
    }

    public function builder(): Builder
    {
        $locations = Auth::user()->locations()->pluck('id')->toArray();
        return DonorBloodRequestResponse::query()
            ->whereIn('location_id', $locations);
    }

    public function bulkActions(): array
    {
        return [
            'confirm' => '?????????????????????? ??????????????????',
        ];
    }

    public function confirm() {
        $donorIds = DonorBloodRequestResponse::whereIn('id', $this->getSelected())->get()->pluck('donor_id')->all();
        Donor::whereIn('id', $donorIds)->update(['last_donorship_date' => Carbon::now()->toDateTimeString()]);
        DonorBloodRequestResponse::whereIn('id', $this->getSelected())->update(['donorship_date' => Carbon::now()->toDateTimeString()]);
        
        foreach ($donorIds as $donorId) {
            $chat = DonorTelegramChat::where('donor_id', $donorId)->get();
            if (config('telegraph.debug_mode')) {
                Log::debug('Thank you', $chat->toArray());
            }
            if (!empty($chat)) {
                $chat->first()
                    ->markdown(__('messages.response.thank_you_for_donorship'))
                    ->send();
            }
        }

        $this->clearSelected();
    }

}
