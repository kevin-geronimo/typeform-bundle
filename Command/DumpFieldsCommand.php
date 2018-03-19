<?php

namespace TypeformBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class DumpFieldsCommand
 * @package App\Command
 */
class DumpFieldsCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'typeform:fields';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('List all typeform\'s fields')
            ->setHelp('This command allows you to list all fields available for a give typeform')
            ->addArgument('id', InputArgument::REQUIRED, 'Typeform ID')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $questions = array();
        $hidden = array();

        try {
            $form = $this->getContainer()->get('typeform.client')->getForm($input->getArgument('id'));
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            exit();
        }

        foreach ($form->questions as $row) {
            $type = explode('_', $row->id);
            if ($type[0] === "hidden") {
                $hidden[$row->field_id] = $row->question;
            } else {
                $questions[$type[1]] = strip_tags($row->question);
            }
        }

        $questions = array_unique($questions);
        array_walk($questions, function (&$q, $i) {
            $q = [$i, substr($q, 0, 60) . ((strlen($q) >= 60) ? ' ...' : '')];
        });
        array_walk($hidden, function (&$h, $i) {
            $h = [$i, $h];
        });


        $io->title(count($questions) . ' unique questions from typeform ID : ' . $input->getArgument('id'));

        $io->table(array('ID', 'Hidden'), $hidden);
        $io->table(array('ID', 'Question'), $questions);
    }
}