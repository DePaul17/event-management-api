<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->reservations()->with('event')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $event = Event::findOrFail($request->event_id);

        if ($request->user()->reservations()->where('event_id', $event->id)->exists()) {
            return response()->json(['message' => 'Vous êtes déjà inscrit à cet événement.'], 409);
        }

        if ($event->reservations()->count() >= $event->max_participants) {
            return response()->json(['message' => 'Événement complet.'], 403);
        }

        $reservation = Reservation::create([
            'user_id'  => $request->user()->id,
            'event_id' => $event->id,
        ]);

        return response()->json([
            'message' => 'Réservation effectuée avec succès.',
            'reservation' => $reservation
        ]);
    }

    public function destroy($id)
    {
        $reservation = Reservation::where('id', $id)->where('user_id', auth()->id())->first();

        if (! $reservation) {
            return response()->json(['message' => 'Réservation non trouvée.'], 404);
        }

        $reservation->delete();

        return response()->json(['message' => 'Réservation annulée avec succès.']);
    }

    public function byEvent($eventId)
    {
        $event = Event::findOrFail($eventId);

        $reservations = $event->reservations()->with('user')->get();

        return response()->json([
            'event' => $event->title,
            'total_reservations' => $reservations->count(),
            'participants' => $reservations
        ]);
    }

}
