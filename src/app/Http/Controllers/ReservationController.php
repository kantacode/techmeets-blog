<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // 予約一覧：GET /reservations
    public function index()
    {
        $reservations = Reservation::with('event')->latest()->paginate(10);
        return view('reservations.index', compact('reservations'));
    }

    // 予約を保存：POST /reservations
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id'    => 'required|exists:events,id',
            'name'        => 'required|max:50',
            'email'       => 'required|email',
            'people'      => 'required|integer|min:1',
            'reserved_at' => 'required|date',
        ]);

        Reservation::create($validated);

        return redirect()->route('reservations.index')
            ->with('success', '予約しました');
    }

    // 予約キャンセル：DELETE /reservations/{reservation}
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('success', '予約をキャンセルしました');
    }
}
