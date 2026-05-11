<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Services\MessageNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function __construct(private MessageNotificationService $messageNotificationService)
    {
    }

    public function adminIndex()
    {
        $user = Auth::user();

        Message::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $receivedMessages = Message::with(['sender', 'recipient'])
            ->where('recipient_id', $user->id)
            ->latest()
            ->get();

        $sentMessages = $user->isAdmin()
            ? $this->summarizeSentMessages(
                Message::with(['sender', 'recipient'])
                    ->where('sender_id', $user->id)
                    ->latest()
                    ->get()
            )
            : $this->summarizeSentMessages(
                Message::with(['sender', 'recipient'])
                    ->where('sender_id', $user->id)
                    ->latest()
                    ->get()
            );

        $parents = $user->isAdmin()
            ? User::where('role', 'parent')
                ->with('children.class')
                ->orderBy('name')
                ->get()
            : collect();

        $teachers = $user->isAdmin()
            ? User::where('role', 'teacher')
                ->orderBy('name')
                ->get()
            : collect();

        return view('admin.messages.index', compact('receivedMessages', 'sentMessages', 'parents', 'teachers'));
    }

    public function adminStore(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isTeacher(), 403);

        $validated = $request->validate([
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['integer', 'exists:users,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        if ($user->isTeacher()) {
            $adminRecipients = User::where('role', 'admin')->get();

            if ($adminRecipients->isEmpty()) {
                return back()->withErrors(['body' => 'No admin account is available to receive this message right now.'])->withInput();
            }

            $this->createBatchMessages($adminRecipients, $validated['subject'] ?? null, $validated['body']);

            return back()->with('success', 'Your message has been sent to the admin team.');
        }

        $recipientIds = $validated['recipient_ids'] ?? [];

        if (count($recipientIds) === 0) {
            return back()->withErrors(['recipient_ids' => 'Select at least one recipient.'])->withInput();
        }

        $recipients = User::whereIn('id', $recipientIds)->get();

        if ($recipients->count() !== count($recipientIds)) {
            return back()->withErrors(['recipient_ids' => 'Some selected recipients could not be found.'])->withInput();
        }

        $roles = $recipients->pluck('role')->unique();
        if ($roles->count() !== 1 || !in_array($roles->first(), ['parent', 'teacher'], true)) {
            return back()->withErrors(['recipient_ids' => 'Messages can only be sent to parents or teachers, and the selected recipients must be from the same group.'])->withInput();
        }

        $this->createBatchMessages($recipients, $validated['subject'] ?? null, $validated['body'], notifyRecipients: true);

        $roleLabel = $roles->first() === 'parent' ? 'parents' : 'teachers';
        $label = $recipients->count() === 1 ? $recipients->first()->name : $recipients->count() . ' ' . $roleLabel;

        return back()->with('success', "Message sent successfully to {$label}.");
    }

    public function parentIndex()
    {
        $parent = Auth::user();

        Message::where('recipient_id', $parent->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $receivedMessages = Message::with(['sender', 'recipient'])
            ->where('recipient_id', $parent->id)
            ->latest()
            ->get();

        $sentMessages = $this->summarizeSentMessages(
            Message::with(['sender', 'recipient'])
                ->where('sender_id', $parent->id)
                ->latest()
                ->get()
        );

        $children = $parent->children()->with('class')->get();

        return view('parent.messages.index', compact('receivedMessages', 'sentMessages', 'children'));
    }

    public function parentStore(Request $request)
    {
        abort_unless(Auth::user()->isParent(), 403);

        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $adminUsers = User::where('role', 'admin')->get();

        if ($adminUsers->isEmpty()) {
            return back()->withErrors(['body' => 'No admin account is available to receive this message right now.'])->withInput();
        }

        $this->createBatchMessages($adminUsers, $validated['subject'] ?? null, $validated['body']);

        return back()->with('success', 'Your message has been sent to the admin team.');
    }

    private function createBatchMessages(Collection $recipients, ?string $subject, string $body, bool $notifyRecipients = false): void
    {
        $batchId = (string) Str::uuid();

        foreach ($recipients as $recipient) {
            $message = Message::create([
                'batch_id' => $batchId,
                'sender_id' => Auth::id(),
                'recipient_id' => $recipient->id,
                'subject' => $subject ?: null,
                'body' => $body,
            ]);

            if ($notifyRecipients) {
                $message->loadMissing(['sender', 'recipient']);
                $this->messageNotificationService->send($message);
            }
        }
    }

    private function summarizeSentMessages(Collection $messages): Collection
    {
        return $messages
            ->groupBy(fn ($message) => $message->batch_id ?: 'single-' . $message->id)
            ->map(function (Collection $group) {
                $first = $group->sortByDesc('created_at')->first();

                return (object) [
                    'subject' => $first->subject,
                    'body' => $first->body,
                    'created_at' => $first->created_at,
                    'recipients' => $group->pluck('recipient.name')->unique()->values(),
                    'recipient_roles' => $group->pluck('recipient.role')->unique()->values(),
                    'recipient_count' => $group->pluck('recipient_id')->unique()->count(),
                ];
            })
            ->sortByDesc('created_at')
            ->values();
    }
}
