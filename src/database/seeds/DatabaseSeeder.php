<?php

namespace Revys\Revy\Database\Seeds;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LanguageSeeder::class);
        $this->call(PageSeeder::class);
        $this->call(AdminUserSeeder::class);
    }
}
