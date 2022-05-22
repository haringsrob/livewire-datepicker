<?php

namespace Haringsrob\LivewireDatepicker\Dto;

use Carbon\Carbon;

class DatePickerDayData
{
    public function __construct(
        public Carbon $date,
        // The day itself cannot be clicked, but a range can go over it.
        public bool $disabled = false,
        // If this is set to true, when a date picker range starts before this date. It cannot pick a date after.
        public bool $cannotPickOver = false,
        // Classes to apply to individual date.
        public ?string $classes = null,
        // Tooltip only works if https://github.com/ryangjchandler/alpine-tooltip is enabled.
        public ?string $toolTip = null,
    ) {
    }
}
