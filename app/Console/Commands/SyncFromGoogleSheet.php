<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Revolution\Google\Sheets\Facades\Sheets;

use App\Models\Donor;

class SyncFromGoogleSheet extends Command
{
    protected $signature = 'donor:sync-from-google';

    protected $description = 'Synchronize data from Google Sheet';

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $data = Sheets::spreadsheet(config('google.spreadsheet_id'))
              ->sheetById(config('google.sheet_id'))
              ->get();
        var_dump($data);
        $header = $data->pull(0);
        $values = Sheets::collection($header, $data)->toArray();

        return;

        foreach ($values as $i => $row) {
            //skip all records that have Telegram ID set
            if ($row['Telegram ID'] != '') {
                continue;
            }

            $donor = new Donor();
            $insert = [
                'name' => $row["Прізвище, Ім'я"],
                'phone' => $row["Ваш мобільный телефон"],
                'blood_type' => $row["Група крові"],
                'blood_rh' => $row["Резус-фактор"],
                'sheet_row' => $i+1
            ];
            if (!empty($donor['phone'])) {
                $donor['phone'] = preg_replace("/[^0-9]/", "", $donor['phone']);
                if (substr($donor['phone'], 0, 2) == '38') {
                    $donor['phone'] = '+' . $donor['phone'];
                } elseif (substr($donor['phone'], 0, 2) == '80') {
                    $donor['phone'] = '+3' . $donor['phone'];
                }if (substr($donor['phone'], 0, 1) == '0') {
                    $donor['phone'] = '+38' . $donor['phone'];
                }
                $donor->fill($insert);
                $donor->save();
            }
        }
    }
}
