<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Space::create([
            'name' => 'Sala de Juntas A',
            'type' => 'room',
            'description' => 'Sala principal con proyector y pizarra. Capacidad para 10 personas.',
            'capacity' => 10,
            'location' => 'Piso 2, Ala Norte',
            'availability' => [
                'monday' => [['start' => '09:00', 'end' => '18:00']],
                'tuesday' => [['start' => '09:00', 'end' => '18:00']],
                'wednesday' => [['start' => '09:00', 'end' => '13:00']],
                'thursday' => [['start' => '09:00', 'end' => '18:00']],
                'friday' => [['start' => '09:00', 'end' => '20:00']],
            ],
        ]);

        Space::create([
            'name' => 'Escritorio Individual B-12',
            'type' => 'desk',
            'description' => 'Escritorio en zona de coworking tranquila, cerca de la ventana.',
            'capacity' => 1,
            'location' => 'Piso 3, Zona Coworking',
            'availability' => [
                'monday' => [['start' => '08:00', 'end' => '20:00']],
                'tuesday' => [['start' => '08:00', 'end' => '20:00']],
                'wednesday' => [['start' => '08:00', 'end' => '20:00']],
                'thursday' => [['start' => '08:00', 'end' => '20:00']],
                'friday' => [['start' => '08:00', 'end' => '20:00']],
                'saturday' => [['start' => '10:00', 'end' => '16:00']],
            ],
        ]);

        Space::create([
            'name' => 'Salón de Eventos Principal',
            'type' => 'hall',
            'description' => 'Gran salón para conferencias y eventos. Equipado con sistema de audio y video.',
            'capacity' => 150,
            'location' => 'Planta Baja',
            'availability' => [
                'monday' => [['start' => '14:00', 'end' => '21:00']],
                'wednesday' => [['start' => '14:00', 'end' => '21:00']],
                'friday' => [['start' => '14:00', 'end' => '22:00']],
            ],
        ]);
    }
}
