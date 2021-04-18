<?php
declare(strict_types=1);

namespace App\Service;

use JoliCode\Slack\Api\Client;

class ReportUsersWithoutConferencePost
{
    private Client $slackClient;
    private GetUsersWhoWorkedThisWeek $getUsersWhoWorkedThisWeek;
    private GetUsernamesFromUserIds $getUsernamesFromUserIds;

    public function __construct(
        Client $slackClient,
        GetUsersWhoWorkedThisWeek $getUsersWhoWorkedThisWeek,
        GetUsernamesFromUserIds $getUsernamesFromUserIds
    ) {
        $this->slackClient = $slackClient;
        $this->getUsersWhoWorkedThisWeek = $getUsersWhoWorkedThisWeek;
        $this->getUsernamesFromUserIds = $getUsernamesFromUserIds;
    }

    public function __invoke() : void
    {
        $usersWhoForgotToPost = $this->getUsersWhoForgotToPost();

        if (count($usersWhoForgotToPost) === 0) {
            return;
        }

        $reminderMessage = sprintf(
            '^ %s',
            implode(', ', ($this->getUsernamesFromUserIds)($usersWhoForgotToPost, true))
        );

        $this->slackClient->chatPostMessage([
            'channel' => 'mike-testing',
            'text' => $reminderMessage,
            'link_names' => true,
        ]);
    }

    private function getUsersWhoForgotToPost() : array
    {
        $usersWhoWorkedThisWeek = ($this->getUsersWhoWorkedThisWeek)();
        $usersWhoPosted = $this->getUsersWhoPosted();

        $usersWhoForgotToPost = [];

        foreach ($usersWhoWorkedThisWeek as $userWhoWorked) {
            if (in_array($userWhoWorked, $usersWhoPosted)) {
                continue;
            }

            $usersWhoForgotToPost[] = $userWhoWorked;
        }

        return $usersWhoForgotToPost;
    }

    /**
     * @return array<string>
     */
    private function getUsersWhoPosted() : array
    {
        $result = $this->slackClient->conversationsHistory([
            'channel' => 'CBBHM360M',
            'oldest' => (string) (time() - 3 * 24 * 60 * 60), // last 24 hours.
        ]);

        $usersWhoPosted = [];

        foreach ($result->getMessages() as $message) {
            if (in_array($message->getUser(), $usersWhoPosted)) {
                continue;
            }

            $usersWhoPosted[] = $message->getUser();
        }

        return $usersWhoPosted;
    }
}