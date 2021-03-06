<?php

namespace App\Http\Livewire;

use App\Models\Donor;
use App\Models\BloodType;
use App\Models\BloodRequest;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\ImageColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\ButtonGroupColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\MultiSelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;
use Illuminate\Support\Facades\Auth;

class RequestsTable extends DataTableComponent
{

    protected $model = BloodRequest::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setAdditionalSelects(['blood_requests.id as id'])
            ->setEagerLoadAllRelationsEnabled()
            ->setSingleSortingDisabled()
            ->setDefaultSort('created_at', 'desc')
            ->setSortingPillsEnabled()
            ->setHideReorderColumnUnlessReorderingEnabled()
            ->setFilterLayoutSlideDown()
            ->setFiltersVisibilityEnabled()
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
            ->setHideBulkActionsWhenEmptyEnabled()
            ->setTableRowUrl(function($row) {
                return '/request-responses?table[filters][blood_request_id]=' . $row->id;
            });
    }

    public function filters(): array
    {
        $locations = Auth::user()->locations()->pluck('name', 'id')->toArray();
        return [
            SelectFilter::make('?????????? ??????????')
                ->options(['' => '?????? ??????????'] + BloodType::BLOOD_TYPES)
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('blood_type_id', $value);
                }),
            SelectFilter::make('??????????????')
                ->options(['' => '?????? ??????????????'] + $locations)
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('blood_type_id', $value);
                }),
            /*DateFilter::make('?????????????? ?????????????????? ????')
                ->config([
                    'min' => '2020-01-01',
                    'max' => '2021-12-31',
                ])
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('email_verified_at', '>=', $value);
                }),
            DateFilter::make('Verified To')
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('email_verified_at', '<=', $value);
                }),*/
        ];
    }

    public function columns(): array
    {
        return [
            Column::make('????????', 'created_at')
                ->sortable()
                ->format(
                    fn($value, $row, Column $column) => Carbon::parse($value)->locale('uk')->isoFormat('LL LT')
                ),
            Column::make('??????????????', 'location.name')
                ->eagerLoadRelations()
                ->sortable()
                ->searchable(),
            Column::make('?????????? ??????????', 'blood_type_id')
                ->sortable()
                ->searchable()
                ->format(
                    fn($value, $row, Column $column) => !empty($value) ? BloodType::BLOOD_TYPES[$value] : '?'
                ),
            Column::make('??????????????o', 'qty'),
            Column::make('??????????????', 'id')
                ->format(
                    fn($value, $row, Column $column) => $row->donors_count
                ),
            Column::make('????????????????????', 'id')
                ->format(
                    fn($value, $row, Column $column) => $row->responses_count
                ),
            BooleanColumn::make('??????????????', 'id')
                ->setCallback(function(string $value, $row) {
                    return (!empty($row->closed_on) || $row->donors_count > $row->qty);
                }),
            Column::make('???????? ????????????????', 'closed_on')
                ->sortable()
                ->format(
                    fn($value, $row, Column $column) =>!empty($value) ? Carbon::parse($value)->locale('uk')->isoFormat('LL LT') : ''
                ),
        ];
    }

    public function builder(): Builder
    {
        $locations = Auth::user()->locations()->pluck('id')->toArray();
        return BloodRequest::query()
            ->withCount('donors')
            ->withCount('responses')
            ->whereIn('location_id', $locations);
    }

    public function bulkActions(): array
    {
        return [
            'close' => '?????????????? ??????????',
        ];
    }

    public function close()
    {
        BloodRequest::whereIn('id', $this->getSelected())->update(['closed_on' => Carbon::now()->toDateTimeString()]);

        $this->clearSelected();
    }

}
