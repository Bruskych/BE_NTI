<?php

namespace App\Jobs;

use App\Mail\BulkMessageMail;
use App\Models\BulkMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBulkMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const GROUP_ROLE_MAP = [
        'students'      => 'student',
        'mentors'       => 'mentor',
        'companies'     => 'company',
        'team_leaders'  => 'team_leader',
        'evaluators'    => 'evaluator',
        'admins'        => 'admin',
    ];

    public function __construct(public int $bulkMessageId) {}

    public function handle(): void
    {
        $bulkMessage = BulkMessage::find($this->bulkMessageId);

        if (!$bulkMessage || $bulkMessage->isSent()) {
            return;
        }

        $recipients = $this->resolveRecipients($bulkMessage->target_group);

        $bulkMessage->recipients()->syncWithoutDetaching($recipients->pluck('id'));

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(
                new BulkMessageMail($bulkMessage->subject, $bulkMessage->body)
            );

            $bulkMessage->recipients()->updateExistingPivot($recipient->id, [
                'delivered_at' => now(),
            ]);
        }

        $bulkMessage->update(['sent_at' => now()]);
    }

    private function resolveRecipients(string $targetGroup): Collection
    {
        if ($targetGroup === 'all') {
            return User::query()->get();
        }

        $role = self::GROUP_ROLE_MAP[$targetGroup] ?? null;

        if (!$role) {
            return new Collection();
        }

        return User::role($role)->get();
    }
}
