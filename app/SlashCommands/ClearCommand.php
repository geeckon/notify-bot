<?php

namespace App\SlashCommands;

use App\Models\Notification;
use Carbon\Carbon;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\SlashCommand;

class ClearCommand extends SlashCommand
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'clear';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Izdzēst vai izmainīt notifikāciju.';

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
            'name' => 'new-user',
            'description' => 'Lietotājs, kuru ielikt iepriekšējā lietotāja vietā',
            'type' => Option::USER,
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
                    ->title('Clear Command')
                    ->content("Failed to parse timestamp")
                    ->build()
            );
        }

        $notification = Notification::where('user_id', $this->value('user'))
            ->whereDate('notify_at', $timestamp)
            ->first();

        if (!$notification) {
            $interaction->respondWithMessage(
                $this
                    ->message()
                    ->title('Clear Command')
                    ->content('Notifikācija netika atrast')
                    ->build()
            );
        }

        if ($this->value('new-user')) {
            $oldNick = $notification->nick;
            $notification->user_id = $this->value('new-user');
            $notification->nick = $this->discord()->guilds->first()->members->get('id', $this->value('new-user'));
            $notification->save();

            $message = "Notifikācija nomainīta no $oldNick uz $notification->nick\nDatums: " . $notification->notify_at->toDateString();
            if ($notification->time_slot) {
                $message .= "\nLaiks: " . $notification->time_slot;
            }

            $interaction->respondWithMessage(
                $this
                    ->message()
                    ->title('Clear Command')
                    ->content($message)
                    ->build()
            );
        } else {
            $message = "Dzēsta notifikācija lietotājam $notification->nick\nDatums: " . $notification->notify_at->toDateString();
            if ($notification->time_slot) {
                $message .= "\nLaiks: " . $notification->time_slot;
            }

            $notification->delete();

            $interaction->respondWithMessage(
                $this
                    ->message()
                    ->title('Clear Command')
                    ->content($message)
                    ->build()
            );
        }
    }

    /**
     * The command interaction routes.
     */
    public function interactions(): array
    {
        return [
            'wave' => fn (Interaction $interaction) => $this->message('👋')->reply($interaction),
        ];
    }
}
