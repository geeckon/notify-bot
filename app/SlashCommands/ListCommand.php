<?php

namespace App\SlashCommands;

use App\Models\Notification;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\SlashCommand;

class ListCommand extends SlashCommand
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'list';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Parādīt visas turpmākās notifikācijas.';

    /**
     * The command options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The permissions required to use the command.
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * Indicates whether the command requires admin permissions.
     *
     * @var bool
     */
    protected $admin = false;

    /**
     * Indicates whether the command should be displayed in the commands list.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * The guild the command belongs to.
     *
     * @var string
     */
    protected $guild = '1041660108649803796';

    /**
     * Handle the slash command.
     *
     * @param  \Discord\Parts\Interactions\Interaction  $interaction
     * @return mixed
     */
    public function handle($interaction)
    {
        $notifications = Notification::whereNot('sent')->orderBy('notify_at', 'asc')->get();

        $messages = array_map(function($notification) {
            $message = $notification->nick . " " . $notification->notify_at->toDateString();
            if ($notification->time_slot) {
                $message .=  " " . $notification->time_slot;
            }

            return $message;
        }, $notifications);

        $interaction->respondWithMessage(
            $this
                ->message()
                ->title('List Command')
                ->content(implode("\n", $messages))
                ->build()
        );
    }

    /**
     * The command interaction routes.
     */
    public function interactions(): array
    {
        return [
        ];
    }
}
