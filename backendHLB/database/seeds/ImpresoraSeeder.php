<?php

use Illuminate\Database\Seeder;
use App\Models\Impresora;

class ImpresoraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('impresoras')->delete();

        Impresora::create([
            'tipo' => 'Multifuncional',
            'tinta' => 'Epson 664',
            'cartucho' => '4 colores',
            'id_equipo' => 8
        ]);
        Impresora::create([
            'tipo' => 'Matricial',
            'tinta' => 'Negro',
            'cartucho' => 'Cinta',
            'id_equipo' => 9
        ]);
        Impresora::create([
            'tipo' => 'Brazaletes',
            'tinta' => 'Negro',
            'cartucho' => 'Toner',
            'id_equipo' => 10
        ]);
        Impresora::create([
            'tipo' => 'Impresora',
            'tinta' => '210-211',
            'cartucho' => 'Negro y color',
            'id_equipo' =>11
        ]);
        Impresora::create([
            'tipo' => 'Escáner',
            'tinta' => '',
            'cartucho' => '',
            'id_equipo' => 12
        ]);      
    }
}
