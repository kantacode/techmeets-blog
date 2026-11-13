<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    // イベント一覧：GET /events
    public function index()
    {
        $events = Event::orderBy('event_date')->paginate(10);
        return view('events.index', compact('events'));
    }

    // イベント詳細＋予約フォーム：GET /events/{event}
    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }
}