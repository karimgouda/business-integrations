<?php

namespace App\Http\Controllers;

use App\Models\MoodEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoodEntryController extends Controller
{
    public function index()
    {
        $entries = Auth::user()->moodEntries()->latest()->get();
        return view('mood.index', compact('entries'));
    }

    public function create()
    {
        return view('mood.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'mood' => 'required|in:happy,sad,angry,anxious,calm',
            'note' => 'nullable|string',
        ]);

        MoodEntry::create([
            'user_id' => Auth::id(),
            'mood' => $request->mood,
            'note' => $request->note,
            'entry_date' => now()->toDateString(),
        ]);

        return redirect()->route('mood.index')->with('success', 'Mood entry saved!');
    }

}
