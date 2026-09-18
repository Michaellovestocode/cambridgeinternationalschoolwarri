<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\StaffAttendanceEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RepairFingerprintClockIns extends Command
{
    protected $signature = 'attendance:repair-fingerprint-clock-ins
                            {--date= : Attendance date to inspect (YYYY-MM-DD; defaults to today)}
                            {--apply : Apply the repair. Without this option, only a preview is shown.}';

    protected $description = 'Move F-G495 fingerprint first punches mistakenly saved as clock-outs into clock-ins.';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->toDateString()
            : today()->toDateString();

        $recordIds = StaffAttendanceEvent::query()
            ->whereDate('punched_at', $date)
            ->where('direction', 'out')
            ->whereNotNull('attendance_record_id')
            ->pluck('attendance_record_id');

        $records = AttendanceRecord::with('user')
            ->whereDate('attendance_date', $date)
            ->whereIn('id', $recordIds)
            ->whereNull('check_in_at')
            ->whereNotNull('check_out_at')
            ->where('source', 'f-g495')
            ->orderBy('check_out_at')
            ->get();

        if ($records->isEmpty()) {
            $this->info("No affected fingerprint clock-ins found for {$date}.");
            return self::SUCCESS;
        }

        $this->table(['Record', 'Staff', 'Mistaken clock-out', 'New arrival status'], $records->map(function (AttendanceRecord $record) {
            $arrivalStatus = $record->check_out_at->format('H:i') <= '07:15' ? 'on_time' : 'late';

            return [
                $record->id,
                $record->user?->name ?? "User #{$record->user_id}",
                $record->check_out_at->format('g:i A'),
                $arrivalStatus,
            ];
        })->all());

        if (! $this->option('apply')) {
            $this->warn('Preview only: no attendance records were changed. Re-run with --apply after reviewing this list.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($records) {
            foreach ($records as $record) {
                $clockIn = $record->check_out_at;
                $existingNotes = trim((string) $record->notes);
                $repairNote = 'Corrected F-G495 fingerprint punch from clock-out to clock-in.';

                $record->update([
                    'check_in_at' => $clockIn,
                    'check_out_at' => null,
                    'arrival_status' => $clockIn->format('H:i') <= '07:15' ? AttendanceRecord::ARRIVAL_ON_TIME : AttendanceRecord::ARRIVAL_LATE,
                    'departure_status' => null,
                    'notes' => $existingNotes === '' ? $repairNote : "{$existingNotes}\n{$repairNote}",
                ]);
            }
        });

        $this->info("Corrected {$records->count()} attendance record(s) for {$date}.");

        return self::SUCCESS;
    }
}
