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

        $header = $data->pull(0);
        $values = Sheets::collection($header, $data)->toArray();

        foreach ($values as $i => $row) {

            $insert = [
                'name' => trim($row["Прізвище, Ім'я"], "'"),
                'phone' => trim($row["Ваш мобільный телефон"], "'"),
            ];
            if (!empty($insert['phone'])) {
                $insert['phone'] = preg_replace("/[^0-9]/", "", $insert['phone']);
                if (strpos($insert['phone'], ',') !== false) {
                    $phones = explode(',', $insert['phone']);
                    $insert['phone'] = $phones[0];
                }
                if (substr($insert['phone'], 0, 2) == '38') {
                    $insert['phone'] = '+' . $insert['phone'];
                } elseif (substr($insert['phone'], 0, 2) == '80') {
                    $insert['phone'] = '+3' . $insert['phone'];
                } elseif (substr($insert['phone'], 0, 1) == '0') {
                    $insert['phone'] = '+38' . $insert['phone'];
                } elseif (strlen($insert['phone']) == 9) {
                    $insert['phone'] = '+380' . $insert['phone'];
                }
                $blood_type = trim($row["Група крові"], "'");
                $blood_rh = trim($row["Резус-фактор"], "'");
                switch ($blood_type) {
                    case 'I (1)':
                        $insert['blood_type_id'] = '10';
                        break;
                    case 'II (2)':
                        $insert['blood_type_id'] = '20';
                        break;
                    case 'III (3)':
                        $insert['blood_type_id'] = '30';
                        break;
                    case 'IV (4)':
                        $insert['blood_type_id'] = '40';
                        break;
                    default:
                        $insert['blood_type_id'] = null;
                        break;
                }
                if (empty($blood_rh) || $blood_rh == 'Не знаю свою групу крові') {
                    $insert['blood_type_id'] = null;
                } else {
                    if (!empty($insert['blood_type_id']) && strpos($blood_rh, '+') === false) {
                        //negative Rh
                        $insert['blood_type_id'] += 1;
                    }
                }
                //skip if the phone is already in DB
                $donor = Donor::where('phone', $insert['phone'])->first();
                if(empty($donor)) {
                    $donor = new Donor();
                    $donor->fill($insert);
                    $donor->save();
                }
            }
        }
    }
}
