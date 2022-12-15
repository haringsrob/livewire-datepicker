<div class="{{ $this->wrapperClasses() }}">
  <div class="flex items-center text-gray-900">
    <button type="button"
            wire:click="goPreviousMonth"
            class="flex flex-none items-center justify-center rounded bg-gray-100 p-1.5 text-gray-400 hover:text-gray-500">
      <span class="sr-only">@lang('Previous month')</span>
      <span class="text-2xl">
        < </span>
    </button>
    <div class="mx-auto font-semibold">{{ $monthName }} {{ $year }}</div>
    <button type="button"
            wire:click="goNextMonth"
            class="flex flex-none items-center justify-center rounded bg-gray-100 p-1.5 text-gray-400 hover:text-gray-500">
      <span class="sr-only">@lang('Next month')</span>
      <span class="text-2xl">
        >
      </span>
    </button>
  </div>
  <div class="isolate grid grid-cols-7 gap-y-1">
    @foreach ($weekDays as $index => $name)
      <div class="flex justify-center">
        {{ \Illuminate\Support\Str::substr($name, 0, 1) }}
      </div>
    @endforeach
    @foreach ($dates as $index => $day)
      @php
        /** @var \Haringsrob\LivewireDatepicker\Dto\DatePickerDayData $dayData */
        $dayData = $this->getAvailabilityFor($day);
      @endphp
      @if ($loop->first)
        @php
            $skipDays = $this->startWeekOnSunday ? 7 : 6;
        @endphp
        @for ($i = 0; $i < ($day->dayOfWeek === 0 ? $skipDays : ($day->dayOfWeek - ($this->startWeekOnSunday ? 0 : 1))); $i++)

          <div></div>
        @endfor
      @endif
      <div
          wire:key="{{$dayData->date->format('ymd')}}"
          class="@if ($this->type === self::TYPE_RANGE_SINGLE) !rounded-l-full !rounded-r-full @endif @if ($this->isStartRange($day)) bg-primary-300 rounded-l-full
                @elseif ($this->isEndRange($day)) bg-primary-300 rounded-r-full
                @elseif ($this->isInRange($day)) bg-primary-100 @endif @if (!$dayData->disabled && !$this->isDisabled($day)) cursor-pointer
                @else
                    cursor-not-allowed
                    opacity-25 @endif py-1.5 text-gray-700 hover:bg-gray-100 focus:z-10"
           @if ($dayData->toolTip) x-tooltip.raw.html="{{ $dayData->toolTip }}" @endif
           @if (!$dayData->disabled && !$this->isDisabled($day)) wire:click="triggerDate('{{ $day->format(config('livewire-datepicker.event_date_format')) }}')" @endif>
        <div
             class="@if ($this->isSelected($day)) border-2 !border-primary-900 text-white shadow-lg
                    @elseif ($day->isToday()) font-bold @endif {{ $dayData->classes ?? '' }} mx-auto flex h-7 w-7 items-center justify-center rounded-full">
          {{ $day->format('j') }}
        </div>
      </div>
    @endforeach
  </div>
</div>
