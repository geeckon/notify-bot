<?php

namespace App\SlashCommands;

use App\Models\Notification;
use Carbon\Carbon;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\SlashCommand;

class SwapCommand extends SlashCommand
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'swap';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Samainīt vietām vokālās nodarbības.';

    /**
     * The command options.
     *
     * @var array
     */
    protected $options = [
        [
            'name' => 'user1',
            'description' => 'Lietotājs 1',
            'type' => Option::USER,
            'required' => true,
        ],
        [
            'name' => 'date1',
            'description' => 'Datums 1. Formāts: DD.MM (piemēram, 17.02)',
            'type' => Option::STRING,
            'required' => true,
        ],
        [
            'name' => 'user2',
            'description' => 'Lietotājs 2',
            'type' => Option::USER,
            'required' => true,
        ],
        [
            'name' => 'date2',
            'description' => 'Datums 2. Formāts: DD.MM (piemēram, 17.02)',
            'type' => Option::STRING,
            'required' => true,
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
        $nicks = [];
        $notifications = [];
        $timestamps = [];

        try {
            $timestamp = Carbon::parse($this->value('date1') . '.' . Carbon::now()->year);

            if ($timestamp->isPast()) {
                $timestamp = $timestamp->addYear();
            }
            $timestamps[] = $timestamp;

            $timestamp = Carbon::parse($this->value('date2') . '.' . Carbon::now()->year);

            if ($timestamp->isPast()) {
                $timestamp = $timestamp->addYear();
            }
            $timestamps[] = $timestamp;
        } catch (\Exception $e) {
            $interaction->respondWithMessage(
                $this
                    ->message()
                    ->title('Notify Command')
                    ->content("Failed to parse timestamp")
                    ->build()
            );
        }

        $nicks[] = $this->discord()->guilds->first()->members->get('id', $this->value('user1'));
        $nicks[] = $this->discord()->guilds->first()->members->get('id', $this->value('user2'));

        $notifications[] = Notification::where([
            ['sent', false],
            ['nick', $nicks[0]],
        ])->whereDate('notify_at', $timestamps[0])->first();
        $notifications[] = Notification::where([
            ['sent', false],
            ['nick', $nicks[1]],
        ])->whereDate('notify_at', $timestamps[1])->first();

        if (!($notifications[0] && $notifications[1])) {
            $interaction->respondWithMessage(
                $this
                    ->message()
                    ->title('Notify Command')
                    ->content("Didn't find both notifications to swap")
                    ->build()
            );
        }

        $notifications[0]->nick = $nicks[1];
        $notifications[1]->nick = $nicks[0];

        $message = "Samainītas notifikācijas. Jaunās notifikācijas:" .
            "\n\nLietotājam {$notifications[0]->nick}\nPedagogs: "
            . $notifications[0]->teacher . "\nDatums: " . $notifications[0]->notify_at->toDateString();
        if ($notifications[0]->time_slot) {
            $message .= "\nLaiks: " . $notifications[0]->time_slot;
        }
        $message .= "\n\nLietotājam {$notifications[0]->nick}\nPedagogs: "
            . $notifications[1]->teacher . "\nDatums: " . $notifications[0]->notify_at->toDateString();
        if ($notifications[1]->time_slot) {
            $message .= "\nLaiks: " . $notifications[1]->time_slot;
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
