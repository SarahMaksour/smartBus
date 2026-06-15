<?php

namespace App\Console\Commands;

use App\Models\Bus;
use App\Models\BusLocation;
use App\Models\GpsDevice;
use App\Models\GpsLog;
use Illuminate\Console\Command;

class GpsTcpServer extends Command
{
    protected $signature   = 'gps:serve {--port=14296}';
    protected $description = 'TK103 GPS TCP Server';

    public function handle(): void
    {
        $port = (int) $this->option('port');

        $this->info("🚀 GPS Server running on port $port");

        $socket = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);

        if (! $socket) {
            $this->error("❌ Error: $errstr ($errno)");
            return;
        }

        while (true) {
            $client = @stream_socket_accept($socket, -1);
            if (! $client) continue;

            $data = fread($client, 2048);

            if ($data) {
                $this->process($client, trim($data));
            }

            fclose($client);
        }
    }

    private function process($client, string $data): void
    {
        $this->info("📩 RAW: $data");

        // Login packet
        if (str_starts_with($data, '##')) {
            fwrite($client, "LOAD");
            $this->info("🔐 LOGIN packet - replied LOAD");
            return;
        }

        $parsed = $this->parseTK103($data);

        if (! $parsed) {
            $this->warn("❌ Unrecognized packet");
            fwrite($client, "ON");
            return;
        }

        $this->info("📍 IMEI: {$parsed['imei']}");
        $this->info("📍 LAT: {$parsed['lat']} LNG: {$parsed['lng']}");
        $this->info("🚗 SPEED: {$parsed['speed']} km/h");

        // خزن بقاعدة البيانات
        $this->saveLocation($parsed);

        fwrite($client, "ON");
    }

   private function parseTK103(string $data): ?array
{
    // HQ Protocol: *HQ,IMEI,V1,HHMMSS,A,lat,N,lng,E,speed,course,date,...#
    if (str_starts_with($data, '*HQ')) {
        return $this->parseHQ($data);
    }

    // TK103 Protocol القديم
    $parts = explode(',', $data);

    if (count($parts) < 10) {
        return null;
    }

    $imei    = trim(str_replace(['imei:', 'tracker'], '', $parts[0]));
    $latRaw  = $parts[5] ?? 0;
    $latDir  = $parts[6] ?? 'N';
    $lngRaw  = $parts[7] ?? 0;
    $lngDir  = $parts[8] ?? 'E';
    $speed   = round((float) ($parts[9] ?? 0) * 1.852, 2);
    $heading = (float) ($parts[10] ?? 0);

    if (! $imei) return null;

    return [
        'imei'    => $imei,
        'lat'     => $this->convertCoord($latRaw, $latDir),
        'lng'     => $this->convertCoord($lngRaw, $lngDir),
        'speed'   => $speed,
        'heading' => $heading,
    ];
}

private function parseHQ(string $data): ?array
{
    // *HQ,867232055998802,V1,092738,A,3612.9842,N,03646.2642,E,0.00,0.00,301024,...#
    $data  = trim($data, '*#');
    $parts = explode(',', $data);

    if (count($parts) < 10) {
        return null;
    }

    $imei   = $parts[1] ?? null;
    $type   = $parts[2] ?? '';   // V0 = heartbeat, V1 = GPS data
    $valid  = $parts[4] ?? 'V';  // A = valid, V = invalid

    // V0 = heartbeat packet (ما في GPS data)
    if ($type === 'V0') {
        $this->info("💓 Heartbeat من $imei");
        return null;
    }

    // لو الإشارة مش valid
    if ($valid !== 'A') {
        $this->warn("⚠️ إشارة GPS ضعيفة");
        return null;
    }

    $latRaw  = (float) ($parts[5] ?? 0);
    $latDir  = $parts[6] ?? 'N';
    $lngRaw  = (float) ($parts[7] ?? 0);
    $lngDir  = $parts[8] ?? 'E';
    $speed   = round((float) ($parts[9] ?? 0) * 1.852, 2);
    $heading = (float) ($parts[10] ?? 0);

    if (! $imei) return null;

    return [
        'imei'    => $imei,
        'lat'     => $this->convertCoord($latRaw, $latDir),
        'lng'     => $this->convertCoord($lngRaw, $lngDir),
        'speed'   => $speed,
        'heading' => $heading,
    ];
}
    private function convertCoord($value, string $dir): float
    {
        if (! $value) return 0;

        $deg   = floor($value / 100);
        $min   = $value - ($deg * 100);
        $coord = $deg + ($min / 60);

        if ($dir === 'S' || $dir === 'W') {
            $coord *= -1;
        }

        return round($coord, 7);
    }

    private function saveLocation(array $data): void
    {
        $device = GpsDevice::where('imei', $data['imei'])->first();

        if (! $device) {
            $this->warn("⚠️ جهاز غير معروف IMEI: {$data['imei']}");
            return;
        }

        $bus = Bus::where('gps_device_id', $device->id)
            ->where('status', 'active')
            ->first();

        if (! $bus) {
            $this->warn("⚠️ ما في باص مرتبط بالجهاز: {$data['imei']}");
            return;
        }

        $now = now();

        // حدّث الموقع الحالي
        BusLocation::updateOrCreate(
            ['bus_id' => $bus->id],
            [
                'lat'         => $data['lat'],
                'lng'         => $data['lng'],
                'speed'       => $data['speed'],
                'heading'     => $data['heading'],
                'is_online'   => true,
                'recorded_at' => $now,
            ]
        );

        // خزّن بالتاريخ
        GpsLog::create([
            'bus_id'      => $bus->id,
            'lat'         => $data['lat'],
            'lng'         => $data['lng'],
            'speed'       => $data['speed'],
            'heading'     => $data['heading'],
            'is_online'   => true,
            'recorded_at' => $now,
        ]);

        // حدّث last_seen_at
        $device->update(['last_seen_at' => $now]);

        $this->info("✅ باص {$bus->plate_number}: {$data['lat']}, {$data['lng']} | {$data['speed']} كم/س");
    }
}