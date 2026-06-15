<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GpsTcpServer extends Command
{
    protected $signature = 'gps:serve {--port=14296}';
    protected $description = 'TK103 GPS TCP Server';

    public function handle(): void
    {
        $port = (int) $this->option('port');

        $this->info("🚀 GPS Server running on port $port");

        $socket = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);

        if (!$socket) {
            $this->error("❌ Error: $errstr ($errno)");
            return;
        }

        while (true) {
            $client = @stream_socket_accept($socket, -1);
            if (!$client) continue;

            $data = fread($client, 2048);

            if ($data) {
                $this->process($client, $data);
            }

            fclose($client);
        }
    }

    private function process($client, string $data): void
    {
        $data = trim($data);

        $this->info("📩 RAW: $data");

        // 🔐 Login packet
        if (str_starts_with($data, '##')) {
            fwrite($client, "LOAD"); // مهم جدًا
            $this->info("🔐 LOGIN packet - replied LOAD");
            return;
        }

        // 📡 Parse GPS packet
        $parsed = $this->parseTK103($data);

        if (!$parsed) {
            $this->warn("❌ Unrecognized packet");
            return;
        }

        $this->info("📍 IMEI: {$parsed['imei']}");
        $this->info("📍 LAT: {$parsed['lat']} LNG: {$parsed['lng']}");
        $this->info("🚗 SPEED: {$parsed['speed']} km/h");

        // 📤 رد للجهاز (مهم لاستمرار الإرسال)
        fwrite($client, "ON");
    }

    private function parseTK103(string $data): ?array
    {
        $parts = explode(',', $data);

        if (count($parts) < 10) {
            return null;
        }

        // IMEI
        $imei = str_replace(['imei:', 'tracker'], '', $parts[0]);

        // GPS data
        $valid = $parts[4] ?? '';

        $latRaw = $parts[5] ?? 0;
        $latDir = $parts[6] ?? 'N';

        $lngRaw = $parts[7] ?? 0;
        $lngDir = $parts[8] ?? 'E';

        $speed = (float) ($parts[9] ?? 0);

        return [
            'imei' => $imei,
            'lat' => $this->convertCoord($latRaw, $latDir),
            'lng' => $this->convertCoord($lngRaw, $lngDir),
            'speed' => $speed,
            'valid' => $valid,
        ];
    }

    private function convertCoord($value, $dir)
    {
        if (!$value) return 0;

        $deg = floor($value / 100);
        $min = $value - ($deg * 100);

        $coord = $deg + ($min / 60);

        if ($dir === 'S' || $dir === 'W') {
            $coord *= -1;
        }

        return $coord;
    }
}