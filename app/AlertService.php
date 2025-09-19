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
                ])
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36')
                ->get('https://www.oref.org.il/warningMessages/alert/Alerts.json');
            if($res->successful()) {
                $json = $res->json();
                if($json){
                    Log::error('New Alert', $json);
                    if(in_array($json['cat'], ['10', '1', '14'])) {
                        Http::post('https://app.yeda-phone.co.il/api/alerts/oref/', $json);
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
