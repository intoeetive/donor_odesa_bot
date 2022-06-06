<?php

namespace App\Http\Livewire;

use App\Models\Donor;
use App\Models\BloodType;
use App\Models\BloodRequest;

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
            ->setHideBulkActionsWhenEmptyEnabled();
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Група крові')
                ->options(['' => 'Усі групи'] + BloodType::BLOOD_TYPES)
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('blood_type_id', $value);
                }),
            /*DateFilter::make('Останнє донорство до')
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
            Column::make('Дата', 'created_at')->sortable(),
            Column::make('Локація', 'location.name')
                ->eagerLoadRelations()
                ->sortable()
                ->searchable(),
            Column::make('Група крові', 'blood_type_id')
                ->sortable()
                ->searchable()
                ->format(
                    fn($value, $row, Column $column) => BloodType::BLOOD_TYPES[$value]
                ),
            Column::make('Потрібна кількть', 'qty'),
            Column::make('Дата закриття', 'closed_on')->sortable(),
            Column::make('Донори', 'id')
                ->format(
                    fn($value, $row, Column $column) => var_dump($row)
                    //fn($value, $row, Column $column) => $row->id
                ),
            /*LinkColumn::make('Донори')
                ->title(fn($row) => 'Донори')
                ->location(fn($row) => route('donors.index', $row)),*/
        ];
    }

    public function builder(): Builder
    {
        return BloodRequest::query()
            ->withCount('donors')
            ->when($this->columnSearch['name'] ?? null, fn ($query, $name) => $query->where('users.name', 'like', '%' . $name . '%'))
            ->when($this->columnSearch['email'] ?? null, fn ($query, $email) => $query->where('users.email', 'like', '%' . $email . '%'));
    }

}
