<?php

namespace App\Http\Livewire;

use App\Models\Donor;
use App\Models\BloodType;

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

class DonorsTable extends DataTableComponent
{
    private $maxYear;
    private $minYear;
    
    public function configure(): void
    {
        //$this->setDebugEnabled();
        
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
            ->setPerPage(25)
            ->setHideBulkActionsWhenEmptyEnabled();
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('?????????? ??????????')
                ->options(['' => '?????? ??????????'] + BloodType::BLOOD_TYPES)
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('blood_type_id', $value);
                }),
            SelectFilter::make('???????? ???????? ??????????????')
                ->options([
                    '' => '??????',
                    'y' => '??????',
                    'n' => '????'
                ])
                ->filter(function(Builder $builder, string $value) {
                    $this->maxYear = Carbon::now()->year - 18;
                    $this->minYear = Carbon::now()->year - 64;
                    if ($value == 'y') {
                        $builder->where('birth_year', '<', $this->maxYear)
                            ->where('birth_year', '>', $this->minYear)
                            ->where('weight_ok', 1)
                            ->where('no_contras', 1)
                            ->where(function ($query) {
                                $query->where('last_donorship_date', '<', Carbon::parse('2 month ago')->format('Y-m-d'))
                                    ->orWhereNull('last_donorship_date');
                            });
                    } elseif ($value == 'n') {
                        $builder->where('birth_year', '>=', $this->maxYear)
                            ->orWhere('birth_year', '<=', $this->minYear)
                            ->orWhereNull('birth_year')
                            ->orWhere('weight_ok', 0)
                            ->orWhereNull('weight_ok')
                            ->orWhere('no_contras', 0)
                            ->orWhereNull('weight_ok')
                            ->orWhere('last_donorship_date', '>=', Carbon::parse('2 month ago')->format('Y-m-d'));
                    }
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
            Column::make('????\'??', 'name')
                ->sortable()
                ->searchable(),
            Column::make('??????????????', 'phone')
                ->searchable(),
            Column::make('?????????? ??????????', 'blood_type_id')
                ->sortable()
                ->searchable()
                ->format(
                    fn($value, $row, Column $column) => !empty($value) ? BloodType::BLOOD_TYPES[$value] : '?'
                ),
            Column::make('?????? ????????????????????', 'birth_year')
                ->sortable()
                ->searchable(),
            BooleanColumn::make('???????? ?? ??????????', 'weight_ok')->sortable()->searchable(),
            BooleanColumn::make('?????????????????????????? ??????????', 'no_contras')->sortable()->searchable(),
            Column::make('?????????????? ??????????????????', 'last_donorship_date')->sortable(),
            Column::make('????????????????????????????', 'created_at')
                ->format(
                    fn($value, $row, Column $column) => Carbon::parse($value)->locale('uk')->isoFormat('LL LT')
                )
                ->sortable()
        ];
    }

    public function builder(): Builder
    {
        return Donor::query()
            ->when($this->columnSearch['name'] ?? null, fn ($query, $name) => $query->where('donors.name', 'like', '%' . $name . '%'))
            ->when($this->columnSearch['phone'] ?? null, fn ($query, $email) => $query->where('donors.phone', 'like', '%' . $email . '%'));
    }

}
