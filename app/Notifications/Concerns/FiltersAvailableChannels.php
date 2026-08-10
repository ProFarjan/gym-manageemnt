<?php

namespace App\Notifications\Concerns;

/**
 * Every member-facing notification wants to try mail + sms, but member
 * email is nullable (mobile_number is the only required contact field) —
 * without this filter, via() unconditionally including 'mail' queues a job
 * that silently fails once it reaches the mail channel with nowhere to
 * route to. Filtering here, before the job is even dispatched, means "who
 * gets notified" only ever includes channels this specific recipient can
 * actually receive.
 */
trait FiltersAvailableChannels
{
    /**
     * @param  array<int, string>  $channels
     * @return array<int, string>
     */
    protected function availableChannels(object $notifiable, array $channels): array
    {
        return array_values(array_filter($channels, function (string $channel) use ($notifiable) {
            if ($channel === 'mail') {
                return ! empty($notifiable->email);
            }

            if ($channel === 'sms') {
                return ! empty($notifiable->mobile_number);
            }

            return true;
        }));
    }
}
