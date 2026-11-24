<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Week 7 のイベント予約（/events, /reservations）の Feature Test
 */
class EventReservationTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = Event::create([
            'title'       => 'Laravel 勉強会',
            'description' => 'みんなで学ぶ',
            'event_date'  => '2026-12-20 19:00:00',
        ]);
    }

    private function reservationData(array $overrides = []): array
    {
        return array_merge([
            'event_id'    => $this->event->id,
            'name'        => '予約 太郎',
            'email'       => 'taro@example.com',
            'people'      => 2,
            'reserved_at' => '2026-12-01 10:00:00',
        ], $overrides);
    }

    public function test_event_list_and_detail_are_displayed(): void
    {
        $this->get('/events')->assertOk()->assertSee('Laravel 勉強会');
        $this->get("/events/{$this->event->id}")->assertOk()->assertSee('Laravel 勉強会');
    }

    public function test_reservation_can_be_made(): void
    {
        $response = $this->post('/reservations', $this->reservationData());

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', ['event_id' => $this->event->id, 'name' => '予約 太郎', 'people' => 2]);
    }

    public function test_reservation_list_is_displayed(): void
    {
        Reservation::create($this->reservationData());

        $this->get('/reservations')->assertOk()->assertSee('予約 太郎');
    }

    public function test_reservation_can_be_cancelled(): void
    {
        $reservation = Reservation::create($this->reservationData());

        $response = $this->delete("/reservations/{$reservation->id}");

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }

    // 異常系：存在しないイベントには予約できない
    public function test_reservation_for_missing_event_is_rejected(): void
    {
        $response = $this->post('/reservations', $this->reservationData(['event_id' => 9999]));

        $response->assertSessionHasErrors('event_id');
    }

    // 境界値：人数1人（下限ちょうど）→ OK / 0人 → NG
    public function test_reservation_for_one_person_is_accepted(): void
    {
        $this->post('/reservations', $this->reservationData(['people' => 1]))->assertSessionHasNoErrors();
    }

    public function test_reservation_for_zero_people_is_rejected(): void
    {
        $this->post('/reservations', $this->reservationData(['people' => 0]))->assertSessionHasErrors('people');
    }

    public function test_reservation_with_invalid_email_is_rejected(): void
    {
        $this->post('/reservations', $this->reservationData(['email' => 'not-an-email']))->assertSessionHasErrors('email');
    }

    public function test_reservation_belongs_to_event(): void
    {
        $reservation = Reservation::create($this->reservationData());

        $this->assertTrue($reservation->event->is($this->event));
        $this->assertCount(1, $this->event->reservations);
    }
}
