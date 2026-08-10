<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EventAttendanceExport implements WithMultipleSheets
{
    public function __construct(
        private readonly Event $event,
        private readonly ?User $user = null,
    ) {}

    public function sheets(): array
    {
        return [
            new EventAttendanceTypesSheet($this->event, $this->user),
            new EventAttendanceParticipantsSheet($this->event, $this->user),
        ];
    }
}
