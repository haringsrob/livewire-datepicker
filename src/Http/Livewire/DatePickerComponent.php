<?php

namespace Haringsrob\LivewireDatepicker\Http\Livewire;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Haringsrob\LivewireDatepicker\Dto\DatePickerDayData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

abstract class DatePickerComponent extends Component
{
    public const TYPE_DATEPICKER = 'datepicker';
    public const TYPE_RANGE_PICKER = 'range-picker';
    public const TYPE_RANGE_SINGLE = 'range-single';
    public const TYPE_DISPLAY_ONLY = 'display-only';

    public ?Carbon $activeMonth = null;
    public bool $startWeekOnSunday = false;

    protected string $type = self::TYPE_DISPLAY_ONLY;

    protected ?Collection $availabilityData = null;

    public ?Carbon $disableBefore = null;
    public ?Carbon $disableBeforeOriginal = null;
    public ?Carbon $disableAsOf = null;

    public ?Carbon $startRange = null;
    public ?Carbon $endRange = null;
    public array $selectedDates = [];

    /**
     * This method is called whenever the date range or a new date is set.
     */
    public function onDatesSet(): void
    {
    }

    /**
     * This method is called whenever the date range or a new date is unset.
     */
    public function onDatesUnSet(): void
    {
    }

    /**
     * Set classes to wrap the component with.
     */
    public function wrapperClasses(): string
    {
        return '';
    }

    /**
     * Return a collection of DatePickerDayData objects to set the calendar day properties.
     *
     * Example:
     *
     * return Collection::make([DatePickerDayData(Carbon::now(), true)); // Will disable today.
     */
    public function getAvailabilityData(): Collection
    {
        return new Collection();
    }

    public function goNextMonth(): void
    {
        $this->getActiveMonth()->addMonth();
    }

    public function goPreviousMonth(): void
    {
        $this->getActiveMonth()->subMonth();
    }

    public function getActiveMonth(): Carbon
    {
        if (null === $this->activeMonth) {
            $this->activeMonth = Carbon::now();
        }

        return $this->activeMonth;
    }

    public function isDisabled(Carbon $date): bool
    {
        if ($this->disableBefore && $this->disableAsOf) {
            $isBefore = $date->clone()->isBefore($this->disableBefore->clone()->subDay());
            $isAfter = $date->clone()->isAfter($this->disableAsOf->clone()->subDay());

            return $isBefore || $isAfter;
        }

        if ($this->disableBefore) {
            return $date->isBefore($this->disableBefore->clone()->subDay());
        }

        if ($this->disableAsOf) {
            return $date->isAfter($this->disableAsOf->clone()->subDay());
        }

        return false;
    }

    public function getAvailabilityFor(Carbon $day)
    {
        if (!$this->availabilityData) {
            $this->availabilityData = $this->getAvailabilityData();
        }

        $match = $this->availabilityData->first(function (DatePickerDayData $dayData) use ($day) {
            return $dayData->date->isSameDay($day);
        });

        if ($match) {
            return $match;
        }

        return new DatePickerDayData($day);
    }

    public function isStartRange(Carbon $date): bool
    {
        return $this->startRange && $this->startRange->isSameDay($date);
    }

    public function isEndRange(Carbon $date): bool
    {
        return $this->endRange && $this->endRange->isSameDay($date);
    }

    public function isInRange(Carbon $date): bool
    {
        return $this->endRange && $this->startRange && $date->isBetween(
                $this->startRange->startOfDay(),
                $this->endRange->endOfDay()
            );
    }

    public function isSelected(Carbon $date): bool
    {
        return isset($this->selectedDates[$date->format(config('livewire-datepicker.event_date_format'))]);
    }

    public function triggerDate(string $date): void
    {
        $dateCarbon = Carbon::createFromFormat(config('livewire-datepicker.event_date_format'), $date);
        if ($this->type === self::TYPE_RANGE_SINGLE) {
            $this->startRange = $dateCarbon->midDay();
            $this->endRange = $dateCarbon->midDay();
            $this->onDatesSet();
            return;
        }
        if ($this->type === self::TYPE_RANGE_PICKER) {
            if ($this->startRange && $this->endRange) {
                $this->startRange = null;
                $this->endRange = null;
            }
            // Set the day that is not yet set.
            if (!$this->startRange) {
                $this->disableBeforeOriginal = $this->disableBefore;
                $this->disableBefore = $dateCarbon;
                $this->startRange = $dateCarbon;
            } elseif (!$this->endRange) {
                if ($this->startRange->isSameDay($dateCarbon)) {
                    $this->startRange = null;
                } else {
                    $this->endRange = $dateCarbon;
                }
                $this->disableBefore = $this->disableBeforeOriginal;
                $this->disableAsOf = null;
            }

            // Emit an event.
            if ($this->startRange && $this->endRange) {
                $this->onDatesSet();
            } else {
                $this->onDatesUnSet();
            }
            return;
        }

        if ($this->type === self::TYPE_DATEPICKER) {
            if (isset($this->selectedDates[$dateCarbon->format(config('livewire-datepicker.event_date_format'))])) {
                unset($this->selectedDates[$dateCarbon->format(config('livewire-datepicker.event_date_format'))]);
            } else {
                $this->selectedDates[$dateCarbon->format(
                    config('livewire-datepicker.event_date_format')
                )] = $dateCarbon;
            }
            $this->onDatesSet();
        }
    }

    /**
     * Sets the disableAsOf based of the current availability data.
     */
    public function setDisableAsOf(): void
    {
        if (!$this->startRange || $this->endRange) {
            return;
        }

        $datesAfterStart = collect();

        if (!$this->availabilityData) {
            $this->availabilityData = $this->getAvailabilityData();
        }

        $this->availabilityData->each(function (DatePickerDayData $data) use ($datesAfterStart) {
            if ($data->cannotPickOver && $data->date->isAfter($this->startRange)) {
                $datesAfterStart->add($data);
            }
        });

        $datesAfterStart = $datesAfterStart->sort(function (DatePickerDayData $data1, DatePickerDayData $data2) {
            return $data1->date->isAfter($data2->date);
        });

        if ($datesAfterStart->isNotEmpty()) {
            $this->disableAsOf = $datesAfterStart->first()->date;
        }
    }

    protected function getWeekDaysInOrder(bool $startOnSunday = false): array
    {
        if ($startOnSunday) {
            return [
                CarbonInterface::SUNDAY => __('Sunday'),
                CarbonInterface::MONDAY => __('Monday'),
                CarbonInterface::TUESDAY => __('Tuesday'),
                CarbonInterface::WEDNESDAY => __('Wednesday'),
                CarbonInterface::THURSDAY => __('Thursday'),
                CarbonInterface::FRIDAY => __('Friday'),
                CarbonInterface::SATURDAY => __('Saturday'),
            ];
        }

        return [
            CarbonInterface::MONDAY => __('Monday'),
            CarbonInterface::TUESDAY => __('Tuesday'),
            CarbonInterface::WEDNESDAY => __('Wednesday'),
            CarbonInterface::THURSDAY => __('Thursday'),
            CarbonInterface::FRIDAY => __('Friday'),
            CarbonInterface::SATURDAY => __('Saturday'),
            CarbonInterface::SUNDAY => __('Sunday'),
        ];
    }

    public function render(): View
    {
        $startPeriod = $this->getActiveMonth()->clone()->startOfMonth();
        $endPeriod = $this->getActiveMonth()->clone()->endOfMonth();

        $dates = CarbonPeriod::create($startPeriod, $endPeriod)->toArray();

        $order = $this->getWeekDaysInOrder($this->startWeekOnSunday);

        $this->setDisableAsOf();

        return view('livewire-datepicker::livewire.date-picker', [
            'year' => $startPeriod->year,
            'monthName' => $startPeriod->monthName,
            'weekDays' => $order,
            'dates' => $dates,
        ]);
    }
}
