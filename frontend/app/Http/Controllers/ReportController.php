<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * POST /api/reports — file a report against a user or conversation.
     * Admins triage these in the administrator panel; never exposes message content.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'reported_user_id' => 'nullable|integer|exists:users,id',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
            'message_id' => 'nullable|integer|exists:messages,id',
            'reason' => 'required|string|max:40',
            'details' => 'nullable|string|max:2000',
        ]);

        if (empty($data['reported_user_id']) && empty($data['conversation_id'])) {
            return response()->json(['success' => false, 'message' => 'Nothing to report.'], 422);
        }

        if (! empty($data['conversation_id'])) {
            $conversation = Conversation::findOrFail($data['conversation_id']);
            abort_unless($conversation->users()->where('user_id', $user->id)->exists(), 403);
        }

        $report = $user->reportsFiled()->create([
            'reported_user_id' => $data['reported_user_id'] ?? null,
            'conversation_id' => $data['conversation_id'] ?? null,
            'message_id' => $data['message_id'] ?? null,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'open',
        ]);

        return response()->json(['success' => true, 'data' => ['id' => $report->id]], 201);
    }
}
