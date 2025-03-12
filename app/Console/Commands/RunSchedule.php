<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Laracord\Console\Commands\Command;

class RunSchedule extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:run-schedule';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notifications = Notification::all();
        foreach ($notifications as $notification) {
            $message = $notification->nick . ", Tev rīt ir vokālā nodarbība";
            if ($notification->time_slot) {
                $message .= "\n laiks: " . $notification->time_slot;
            }

            $this->bot
                ->message($message)
                ->body("<@$notification->user_id>\ndef")
                ->send(config('discord.guildId'));
        }
    }
}
