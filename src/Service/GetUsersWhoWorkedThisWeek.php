<?php
declare(strict_types=1);

namespace App\Service;

use JoliCode\Slack\Api\Client;

/**
 * Fuzzy logic to determine users who worked this week based on whether they
 * posted on #suurlahetysto channel in the last 5 days.
 */
class GetUsersWhoWorkedThisWeek
{
    // Number of messages in #suurlahetysto channel that qualify to count
    // user as if he worked this week.
    const MESSAGE_COUNT_THRESHOLD = 3;

    private Client $slackClient;

    public function __construct(Client $slackClient)
    {
        $this->slackClient = $slackClient;
    }

    public function __invoke() : array
    {
        $result = $this->slackClient->conversationsHistory([
            'channel' => 'C03V4DD2J', // suurlahetysto
            'oldest' => (string) (time() - 5 * 24 * 60 * 60), // last 5 days.
            'limit' => 500, // should be enough to make single query.
        ]);

        $usersWhoPosted = [];

        foreach ($result->getMessages() as $message) {
            $usersWhoPosted[$message->getUser()] = ($usersWhoPosted[$message->getUser()] ?? 0) + 1;
        }

        return array_keys(
            array_filter(
                $usersWhoPosted,
                fn (int $messageCount) => $messageCount > self::MESSAGE_COUNT_THRESHOLD,
            )
        );
    }
}