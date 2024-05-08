<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class WidgetIncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Pemasukan';
    protected static string $color = 'success';

    use InteractsWithPageFilters;
    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? now();

        try {
            $startDate = Carbon::parse($startDate);
        } catch (\Throwable $th) {
            $startDate = null;
        }

        try {
            $endDate = Carbon::parse($endDate);
        } catch (\Throwable $th) {
            $endDate = now();
        }

        $data = Trend::query(Transaction::incomes())
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan Per Bulan',
                    'data' => $data->map(function (TrendValue $value) {
                        return $value->aggregate;
                    }),
                ],
            ],
            'labels' => $data->map(function (TrendValue $value) {
                // Format bulan dan tahun saja
                return Carbon::parse($value->date)->format('M Y');
            }),
        ];
    }
    protected function getType(): string
    {
        return 'line';
    }
}
