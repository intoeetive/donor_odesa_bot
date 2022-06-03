<?php

namespace App\Http\Livewire;

use App\Models\Donor;
use App\Models\BloodType;

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

class DonorsTable extends DataTableComponent
{

    protected $model = Donor::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setAdditionalSelects(['donors.id as id'])
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
            Column::make('Ім\'я', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Телефон', 'phone')
                ->searchable(),
            Column::make('Група крові', 'blood_type_id')
                ->sortable()
                ->searchable()
                ->format(
                    fn($value, $row, Column $column) => BloodType::BLOOD_TYPES[$value]
                ),
            Column::make('Рік народження', 'birth_year')
                ->sortable()
                ->searchable(),
            BooleanColumn::make('Вага в нормі', 'weight_ok')->sortable()->searchable(),
            BooleanColumn::make('Протипоказань немає', 'no_contras')->sortable()->searchable(),
            Column::make('Останнє донорство', 'last_donorship_date')->sortable(),
            Column::make('Зареєстрований', 'created_at')->sortable()
        ];
    }

    public function builder(): Builder
    {
        return Donor::query()
            ->when($this->columnSearch['name'] ?? null, fn ($query, $name) => $query->where('users.name', 'like', '%' . $name . '%'))
            ->when($this->columnSearch['email'] ?? null, fn ($query, $email) => $query->where('users.email', 'like', '%' . $email . '%'));
    }

}
