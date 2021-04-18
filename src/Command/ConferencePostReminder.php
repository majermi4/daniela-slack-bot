<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\ReportUsersWithoutConferencePost;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConferencePostReminder extends Command
{
    private ReportUsersWithoutConferencePost $reportUsersWithoutConferencePost;

    public function __construct(ReportUsersWithoutConferencePost $reportUsersWithoutConferencePost)
    {
        parent::__construct();

        $this->reportUsersWithoutConferencePost = $reportUsersWithoutConferencePost;
    }

    public function configure()
    {
        $this
            ->setName('conference-post-reminder')
            ->setDescription('Reminds people about posting to #conference channel on Friday.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            ($this->reportUsersWithoutConferencePost)();

            $io->success('Message sent');
        } catch (SlackErrorResponse $e) {
            $io->error('Fail to send the message. ' . $e->getMessage());
        }

        return 0;
    }
}