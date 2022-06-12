<?php

namespace App\Http\Livewire;

use App\Models\BloodType;
use App\Models\Location;
use App\Models\BloodRequest;
use App\Models\DonorBloodRequestResponse;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\NumberFilter;

class ResponsesTable extends DataTableComponent
{
    public function configure(): void
    {
        $this->setDebugEnabled();
        
        $this->setPrimaryKey('id')
            //->setReorderEnabled()
            ->setAdditionalSelects([
                'donor_blood_request_responses.id as id',
                'donor_blood_request_responses.blood_request_id as blood_request_id',
                'blood_requests.blood_type_id as blood_type_id',
                'blood_requests.location_id as location_id',
                'blood_requests.created_at AS bloodRequest_created_at', 
                'locations.name AS location_name'])
            ->setEagerLoadAllRelationsEnabled()
            ->setSingleSortingDisabled()
            ->setDefaultSort('confirmation_date', 'desc')
            ->setSortingPillsEnabled()
            ->setHideReorderColumnUnlessReorderingEnabled()
            ->setFilterLayoutSlideDown()
            ->setRememberColumnSelectionDisabled()
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
            ->setHideBulkActionsWhenEmptyEnabled();
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Запит', 'blood_request_id')
                ->options(['' => 'Усі запити'] + BloodRequest::orderByDesc('created_at')->limit(5)->get()->pluck('created_at', 'id')->all())
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('blood_request_id', $value);
                }),
            SelectFilter::make('Локація')
                ->options(['' => 'Усі місця'] + Location::pluck('name', 'id')->all())
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('location_id', $value);
                }),
            SelectFilter::make('Група крові')
                ->options(['' => 'Усі групи'] + BloodType::BLOOD_TYPES)
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('blood_type_id', $value);
                }),
        ];
    }

    public function columns(): array
    {
        return [
            LinkColumn::make('Дата', 'bloodRequest.created_at')
                ->title(fn($row) => Carbon::parse($row->bloodRequest_created_at)->locale('uk')->calendar())
                ->location(fn($row) => '/request-responses?table[filters][blood_request_id]=' . $row->blood_request_id),
            Column::make('Місце', 'location.name')
                ->sortable()
                ->searchable(),
            Column::make(__('ui.blood_type'), 'bloodRequest.blood_type_id')
                ->collapseOnTablet()
                ->sortable()
                ->searchable()
                ->format(fn($value, $row, Column $column) => BloodType::BLOOD_TYPES[$value]),
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
                    fn($value, $row, Column $column) => Carbon::parse($value)->locale('uk')->isoFormat('LL LT')
                ),
        ];
    }

    public function builder(): Builder
    {
        return DonorBloodRequestResponse::query();
    }

    public function bulkActions(): array
    {
        return [
            'confirm' => 'Confirm',
            'export' => 'Export',
        ];
    }
}
