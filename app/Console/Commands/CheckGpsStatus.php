<?php

namespace App\Console\Commands;

use App\Models\GpsDevice;
use App\Models\GpsLog;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckGpsStatus extends Command
{
    protected $signature = 'gps:check';
    protected $description = 'Check if GPS device is TCP online or still SMS mode';

    public function handle(): void
    {
        $devices = GpsDevice::all();

        foreach ($devices as $device) {

            $lastLog = GpsLog::where('bus_id', $device->bus_id)
                ->orderByDesc('recorded_at')
                ->first();

            if (!$lastLog) {
                $this->warn("❌ {$device->imei} → NO DATA (maybe SMS mode or not connected)");
                continue;
            }

            $minutes = Carbon::parse($lastLog->recorded_at)->diffInMinutes(now());

            if ($minutes < 2) {
                $this->info("🟢 {$device->imei} → ONLINE (TCP/GPRS working)");
            }
            elseif ($minutes < 10) {
                $this->warn("🟡 {$device->imei} → WEAK SIGNAL / DELAYED");
            }
            else {
                $this->error("🔴 {$device->imei} → OFFLINE OR STILL SMS MODE");
            }

            $this->line("   Last update: {$lastLog->recorded_at}");
        }
    }
}