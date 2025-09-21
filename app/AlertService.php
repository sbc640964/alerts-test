<?php

namespace App;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlertService
{
    public function __invoke(): void
    {
        try {
            $res = Http::timeout(3)
                ->withHeaders([
                    'Referer' => 'https://www.oref.org.il',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Content-Type' => 'application/json',
                ])
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36')
                ->get('https://www.oref.org.il/warningMessages/alert/Alerts.json');
            if($res->successful()) {
                $body = $res->body();
                $clean = preg_replace('/^\xEF\xBB\xBF|\x00|\x1A/', "", $body);
                $json = json_decode($clean, true);

                if($json){
                    Log::error('New Alert', $json);
                    if(in_array($json['cat'], ['10', '1', '14'])) {
                        Http::post('https://app.yeda-phone.com/api/alerts/oref/', $json);
                    }
                }
            }

            if($res->failed()) {
                Log::error('Failed to fetch alerts: '. $res->status());
            }
        } catch (Exception $e) {
            Log::error('Error fetching alerts: '.$e->getMessage());
        }
    }
}
