<?php

namespace App\SlashCommands;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\SlashCommand;

class NotifyCommand extends SlashCommand
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'notify';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Izveidot notifikāciju par vokālo nodarbību.';

    /**
     * The command options.
     *
     * @var array
     */
    protected $options = [
        [
            'name' => 'user',
            'description' => 'Lietotājs',
            'type' => Option::USER,
            'required' => true,
        ],
        [
            'name' => 'date',
            'description' => 'Datums. Formāts: DD.MM (piemēram, 17.02)',
            'type' => Option::STRING,
            'required' => true,
        ],
        [
            'name' => 'time',
            'description' => 'Laika posms, kad ir nodarbība',
            'type' => Option::STRING,
            'required' => false,
        ],
    ];

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
        try {
            $timestamp = Carbon::parse($this->value('date') . '.' . Carbon::now()->year);

            if ($timestamp->isPast()) {
                $timestamp = $timestamp->addYear();
            }
        } catch (\Exception $e) {
            $interaction->respondWithMessage(
                $this
                    ->message()
                    ->title('Notify Command')
                    ->content("Failed to parse timestamp")
                    ->build()
            );
        }

        $notification = new Notification();
        $notification->user_id = $this->value('user');
        $notification->nick = $this->discord()->guilds->first()->members->get('id', $this->value('user'));
        $notification->notify_at = $timestamp;
        $notification->time_slot = $this->value('time');
        $notification->save();

        $message = "Saglabāta notifikācija lietotājam $notification->nick\nDatums: " . $notification->notify_at->toDateString();
        if ($notification->time_slot) {
            $message .= "\nLaiks: " . $notification->time_slot;
        }

        $interaction->respondWithMessage(
            $this
              ->message()
              ->title('Notify Command')
              ->content($message)
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
