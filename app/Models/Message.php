<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Message Model
 * 
 * Represents an internal messaging system message with sender,
 * recipients, read status, and priority support.
 *
 * @property int $id
 * @property int $sender_id
 * @property int|null $team_id
 * @property string $subject
 * @property string $body
 * @property bool $is_priority
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read User $sender
 * @property-read Team|null $team
 * @property-read \Illuminate\Database\Eloquent\Collection<User> $recipients
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'parent_id',
        'subject',
        'body',
    ];

    /**
     * Get the user who sent the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the users who received the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipients()
    {
        return $this->belongsToMany(User::class, 'message_user');
    }

    /**
     * Get the parent message (if this is a reply).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    /**
     * Get all replies to this message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id')->with('sender', 'replies')->orderBy('created_at', 'asc');
    }

    /**
     * Check if this message is a reply.
     *
     * @return bool
     */
    public function isReply()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get the root message of the thread.
     *
     * @return Message
     */
    public function getRootMessage()
    {
        if ($this->isReply()) {
            return $this->parent->getRootMessage();
        }
        return $this;
    }

    /**
     * Get all messages in this thread (root + all replies recursively).
     *
     * @return \Illuminate\Support\Collection
     */
    public function thread()
    {
        $root = $this->getRootMessage();
        return collect([$root])->merge($this->flattenReplies($root->replies));
    }

    /**
     * Flatten replies recursively.
     *
     * @param \Illuminate\Database\Eloquent\Collection $replies
     * @return \Illuminate\Support\Collection
     */
    private function flattenReplies($replies)
    {
        $flat = collect();
        foreach ($replies as $reply) {
            $flat->push($reply);
            if ($reply->replies->isNotEmpty()) {
                $flat = $flat->merge($this->flattenReplies($reply->replies));
            }
        }
        return $flat;
    }
}
