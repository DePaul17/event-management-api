<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Event::with('category', 'organizer')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'event_date'       => 'required|date',
            'location'         => 'required|string',
            'max_participants' => 'required|integer|min:1',
            'category_id'      => 'required|exists:categories,id',
        ]);

        $event = Event::create([
            'title'            => $request->title,
            'description'      => $request->description,
            'event_date'       => $request->event_date,
            'location'         => $request->location,
            'max_participants' => $request->max_participants,
            'category_id'      => $request->category_id,
            'user_id'          => $request->user()->id, // organisateur
        ]);

        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Event::with('category', 'organizer')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        // Vérifie que c'est l'organisateur
        if ($event->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'event_date'       => 'required|date',
            'location'         => 'required|string',
            'max_participants' => 'required|integer|min:1',
            'category_id'      => 'required|exists:categories,id',
        ]);

        $event->update($request->only([
            'title', 'description', 'event_date',
            'location', 'max_participants', 'category_id',
        ]));

        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if ($event->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Événement supprimé']);
    }
}
