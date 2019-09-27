<?php

namespace App\Models;


namespace App\Models;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportExcel implements ToCollection {

    use Importable;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {
            return [
                'base_date'     => $row[0],
                'metrica_id'    => $row[1],
                'a_0'           => $row[2],
                'a_1'           => $row[3],
                'a_2'           => $row[4],
                'a_3'           => $row[5],
                'a_4'           => $row[6],
            ];
        }
    }
}