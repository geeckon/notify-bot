<?php

namespace App\Services;

use App\Models\Notification;
use Carbon\Carbon;
use Carbon\Exceptions\Exception;
use Laracord\Services\Service;

class NotifierService extends Service
{
    /**
     * The service interval.
     */
    protected int $interval = 43200;

    /**
     * Handle the service.
     */
    public function handle(): void
    {
        $notifyChannel = $this->discord()->getChannel('1349199160544329781');
        $commandChannel = $this->discord()->getChannel('1349156560122806332');

        $sent = 0;

        try {
            $notifications = Notification::where('sent', false)->whereDate('notify_at', Carbon::tomorrow())->get();

            foreach ($notifications as $notification) {
                $message = $notification->nick . ", Tev rīt ir vokālā nodarbība!";
                if ($notification->time_slot) {
                    $message .= "\nLaiks: " . $notification->time_slot;
                }

                $this->bot
                    ->message($message)
                    ->body("<@$notification->user_id>\n")
                    ->send($notifyChannel);

                $notification->sent = true;
                $notification->save();

                $sent++;
            }

            $this->bot
                ->message("Bot is alive. Sent $sent notifications")
                ->send($commandChannel);
        } catch (\Exception $e) {
            $this->bot
                ->message("Bot failed!\n" . $e->getMessage())
                ->body("<@202549981515743232>\n")
                ->send($commandChannel);
        }
    }
}
