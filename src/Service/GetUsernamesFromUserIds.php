<?php
declare(strict_types=1);

namespace App\Service;

use JoliCode\Slack\Api\Client;

class GetUsernamesFromUserIds
{
    private Client $slackClient;

    public function __construct(Client $slackClient)
    {
        $this->slackClient = $slackClient;
    }

    /**
     * @param array<string> $userIds
     * @return array<string>
     */
    public function __invoke(array $userIds, bool $asLinks = false) : array
    {
        $result = $this->slackClient->usersList();

        $usernames = [];
        foreach ($result->getMembers() as $member) {
            if (in_array($member->getId(), $userIds)) {
                $username = $member->getName();
                if ($asLinks) {
                    $username = '@' . $username;
                }
                $usernames[] = $username;
            }
        }

        return $usernames;
    }
}