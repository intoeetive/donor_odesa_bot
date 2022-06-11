<?php

namespace App\Http\Livewire;

use App\Models\BloodType;
use App\Models\DonorBloodRequestResponse;

use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class ResponsesTable extends DataTableComponent
{

    protected $model = DonorBloodRequestResponse::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            //->setReorderEnabled()
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
            TextFilter::make('Name')
                ->config([
                    'maxlength' => 5,
                    'placeholder' => 'Search Name',
                ])
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('donors.name', 'like', '%'.$value.'%');
                }),
            SelectFilter::make('Група крові')
                ->options(['' => 'Усі групи'] + BloodType::BLOOD_TYPES)
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('donors.blood_type_id', $value);
                }),
        ];
    }

    public function columns(): array
    {
        return [
            Column::make(__('ui.donor_name'), 'donor.name')
                ->collapseOnTablet()
                ->sortable()
                ->searchable(),
            Column::make(__('ui.phone'), 'donor.phone')
                ->collapseOnTablet()
                ->searchable(),
            Column::make(__('ui.blood_type'), 'donor.blood_type_id')
                ->collapseOnTablet()
                ->sortable()
                ->searchable()
                ->format(fn($value, $row, Column $column) => BloodType::BLOOD_TYPES[$value]),
            Column::make(__('ui.birthday'), 'donor.birth_year')
                ->collapseOnTablet()
                ->sortable()
                ->searchable(),
            BooleanColumn::make(__('ui.donor_weight'), 'donor.weight_ok')->collapseOnTablet()->searchable(),
            BooleanColumn::make(__('ui.donor_no_contras'), 'donor.no_contras')->collapseOnTablet()->searchable(),
            Column::make(__('ui.donor_last_donorship_date'), 'donor.last_donorship_date')->collapseOnTablet()->sortable(),

            BooleanColumn::make(__('ui.response'), 'no_response_contras')->sortable()->searchable(),
            Column::make(__('ui.confirmation_date'), 'confirmation_date')
        ];
    }

    public function builder(): Builder
    {
        return DonorBloodRequestResponse::query()
            ->when($this->columnSearch['name'] ?? null, fn ($query, $name) => $query->where('users.name', 'like', '%' . $name . '%'))
            ->when($this->columnSearch['email'] ?? null, fn ($query, $email) => $query->where('users.email', 'like', '%' . $email . '%'));
    }

    public function bulkActions(): array
    {
        return [
            'confirm' => 'Confirm',
            'export' => 'Export',
        ];
    }
}
