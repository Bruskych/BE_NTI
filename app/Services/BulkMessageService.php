<?php

namespace App\Services;

use App\Jobs\SendBulkMessage;
use App\Models\AuditEvent;
use App\Models\BulkMessage;
use App\Models\User;

class BulkMessageService
{
    public function create(User $sender, array $data): BulkMessage
    {
        $bulkMessage = BulkMessage::create([
            'sender_id'    => $sender->id,
            'target_group' => $data['target_group'],
            'subject'      => $data['subject'],
            'body'         => $data['body'],
        ]);

        AuditEvent::create([
            'user_id'         => $sender->id,
            'action'          => 'bulk_message_sent',
            'object_type'     => 'bulk_message',
            'object_id'       => $bulkMessage->id,
            'old_values_json' => [],
            'new_values_json' => [
                'target_group' => $bulkMessage->target_group,
                'subject'      => $bulkMessage->subject,
            ],
            'result'          => 'success',
            'created_at'      => now(),
        ]);

        SendBulkMessage::dispatch($bulkMessage->id);

        return $bulkMessage;
    }
}
