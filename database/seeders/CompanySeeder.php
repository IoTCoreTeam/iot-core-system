<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::updateOrCreate(
            ['name' => 'OURANSOFT CORP'],
            [
                'address' => 'Room 1501, 15th Floor, Catbi Plaza 1, Le Hong Phong, Dang Lam, Hai Phong',
                'email' => null,
                'phone' => '0904 050 374',
                'fax' => null,
            ],
        );
    }
}
