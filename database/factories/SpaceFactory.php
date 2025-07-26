<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Lógica para generar datos realistas basados en el tipo de espacio
        $type = fake()->randomElement(['room', 'desk', 'hall']);
        $name = '';
        $capacity = 1;

        switch ($type) {
            case 'room':
                $name = 'Sala de Juntas ' . fake()->randomLetter() . fake()->randomNumber(2);
                $capacity = fake()->numberBetween(4, 12);
                break;
            case 'desk':
                $name = 'Escritorio ' . fake()->randomLetter() . '-' . fake()->randomNumber(2);
                $capacity = 1;
                break;
            case 'hall':
                $name = 'Salón de Eventos ' . fake()->colorName();
                $capacity = fake()->numberBetween(50, 200);
                break;
        }

        return [
            'name' => $name,
            'type' => $type,
            'description' => fake()->sentence(15),
            'capacity' => $capacity,
            'location' => 'Piso ' . fake()->numberBetween(1, 5) . ', Zona ' . fake()->randomLetter(),
            'availability' => [
                'monday'    => [['start' => '09:00', 'end' => '18:00']],
                'tuesday'   => [['start' => '09:00', 'end' => '18:00']],
                'wednesday' => [['start' => '09:00', 'end' => '18:00']],
                'thursday'  => [['start' => '09:00', 'end' => '18:00']],
                'friday'    => [['start' => '09:00', 'end' => '18:00']],
            ],
        ];
    }
}
