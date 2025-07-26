<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Space;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ReservationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ReservationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ReservationRepository();
    }

    /** @test */
    public function it_can_create_a_reservation()
    {
        $user = User::factory()->create();
        $space = Space::factory()->create();
        $startTime = Carbon::now()->addDay();

        $reservationData = [
            'user_id' => $user->id,
            'space_id' => $space->id,
            'reservation_date' => $startTime->toDateString(),
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDay()->addHour(),
            'purpose' => 'Test de creación',
        ];

        $reservation = $this->repository->create($reservationData);

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'purpose' => 'Test de creación',
        ]);
    }

    /** @test */
    public function it_can_update_a_reservation()
    {
        $reservation = Reservation::factory()->create(['purpose' => 'Propósito Original']);

        $updatedData = ['purpose' => 'Propósito Actualizado'];

        $result = $this->repository->update($reservation, $updatedData);

        $this->assertTrue($result);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'purpose' => 'Propósito Actualizado',
        ]);
    }

    /** @test */
    public function it_can_change_a_reservation_status()
    {
        $reservation = Reservation::factory()->create(['status' => 'pending']);

        $this->repository->changeStatus($reservation, 'confirmed');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function it_finds_overlapping_reservations_correctly()
    {
        $space = Space::factory()->create();
        $baseTime = Carbon::now()->addDay()->setHour(10);

        // Reserva base de 10:00 a 11:00
        $baseReservation = Reservation::factory()->create([
            'space_id' => $space->id,
            'start_time' => $baseTime,
            'end_time' => $baseTime->copy()->addHour(),
            'status' => 'confirmed',
        ]);

        // Scenario 1: New booking starts during existing booking (10:30 - 11:30) -> Overlap
        $overlapping = $this->repository->findOverlappingReservations($space->id, $baseTime->copy()->addMinutes(30), $baseTime->copy()->addMinutes(90));
        $this->assertCount(1, $overlapping);

        // Scenario 2: New booking ends during existing booking (09:30 - 10:30) -> Overlap
        $overlapping = $this->repository->findOverlappingReservations($space->id, $baseTime->copy()->subMinutes(30), $baseTime->copy()->addMinutes(30));
        $this->assertCount(1, $overlapping);
        
        // Scenario 3: New reservation wraps around existing one (09:00 - 12:00) -> Overlap
        $overlapping = $this->repository->findOverlappingReservations($space->id, $baseTime->copy()->subHour(), $baseTime->copy()->addHours(2));
        $this->assertCount(1, $overlapping);

        // Scenario 4: New reservation is contained in the existing one (10:15 - 10:45) -> Overlap
        $overlapping = $this->repository->findOverlappingReservations($space->id, $baseTime->copy()->addMinutes(15), $baseTime->copy()->addMinutes(45));
        $this->assertCount(1, $overlapping);

        // Scenario 5: New booking does NOT overlap (11:00 - 12:00) -> No overlap
        $nonOverlapping = $this->repository->findOverlappingReservations($space->id, $baseTime->copy()->addHour(), $baseTime->copy()->addHours(2));
        $this->assertCount(0, $nonOverlapping);

        // Scenario 5: New booking does NOT overlap (11:00 - 12:00) -> No overlap
        $overlappingButExcluded = $this->repository->findOverlappingReservations(
            $space->id, 
            $baseTime->copy()->addMinutes(30), 
            $baseTime->copy()->addMinutes(90), 
            $baseReservation->id
        );
        $this->assertCount(0, $overlappingButExcluded);
    }
    
    /** @test */
    public function it_counts_only_active_user_reservations()
    {
        $user = User::factory()->create();
        Reservation::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
        ]);
        Reservation::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        Reservation::factory()->count(4)->create([
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $count = $this->repository->countActiveUserReservations($user->id);

        $this->assertEquals(9, $count);
    }

    /** @test */
    public function it_gets_reservations_only_for_the_authenticated_user()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Reservation::factory()->count(3)->create(['user_id' => $userA->id]);
        Reservation::factory()->count(2)->create(['user_id' => $userB->id]);

        Auth::login($userA);

        $reservations = $this->repository->getForAuthenticatedUser();

        $this->assertCount(3, $reservations);
        $this->assertEquals($userA->id, $reservations->first()->user_id);
    }
}