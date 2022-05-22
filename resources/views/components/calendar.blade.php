<div {{$attributes}}>
    <div class="flex items-center text-gray-900">
        <button type="button"
                wire:click="goPreviousMonth"
                class="flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500 bg-gray-100 rounded">
            <span class="sr-only">@lang('Next month')</span>
            <x-tabler-chevron-left class="h-10 w-10"/>
        </button>
        <div class="mx-auto font-semibold">{{$monthName}} {{$year}}</div>
        <button type="button"
                wire:click="goNextMonth"
                class="flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500 bg-gray-100 rounded">
            <span class="sr-only">@lang('Next month')</span>
            <x-tabler-chevron-right class="h-10 w-10"/>
        </button>
    </div>
    <div class="grid grid-cols-7 gap-y-1 isolate">
        @foreach($weekDays as $index => $name)
            <div class="flex justify-center">
                {{\Illuminate\Support\Str::substr($name, 0, 1)}}
            </div>
        @endforeach
        @foreach ($dates as $index => $day)
            @php
                /** @var \App\Http\Livewire\Manage\Widgets\Dto\DatePickerDayData $dayData */
                $dayData = $getAvailabilityData($day)
            @endphp
            @if ($loop->first)
                @for($i = 0; $i < ($day->weekday() === 0 ? 6 : $day->weekday() - 1); $i++)
                    <div></div>
                @endfor
            @endif
            <div class="
                py-1.5 text-gray-700 focus:z-10 hover:bg-gray-100
                @if ($isStartRange($day)) bg-primary-300 rounded-l-full
                @elseif ($isEndRange($day)) bg-primary-300 rounded-r-full
                @elseif ($isInRange($day)) bg-primary-100
                @endif
                @if ($click && (!$dayData->disabled && !$isDisabled($day)))
                    cursor-pointer
                @else
                    cursor-not-allowed
                    opacity-25
                @endif
            "
                 @if ($click && (!$dayData->disabled && !$isDisabled($day)))
                     wire:click="{{$click}}('{{$day->format(config('livewire-datepicker.event_date_format'))}}')"
                    @endif
            >
                <div class="
                    mx-auto flex h-7 w-7 items-center justify-center rounded-full
                    @if ($isSelected($day)) bg-primary-500 hover:bg-primary-600 text-white
                    @elseif ($day->isToday()) font-bold @endif
                    {{$dayData->color ?? ''}}
                ">
                    {{$day->format('j')}}
                </div>
            </div>
        @endforeach
    </div>
</div>
