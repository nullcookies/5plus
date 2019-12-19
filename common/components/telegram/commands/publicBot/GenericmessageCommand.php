<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use common\components\telegram\Request;
use common\components\telegram\text\PublicMain;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\TelegramLog;

/**
 * Generic message command
 */
class GenericmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';

    /**
     * @var string
     */
    protected $description = 'Handle generic message';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Execution if MySQL is required but not available
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function executeNoDb()
    {
        //Do nothing
        return Request::emptyResponse();
    }

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();

        if ($message->getText() === PublicMain::TO_MAIN) {
            return $this->telegram->executeCommand('start');
        }

        //If a conversation is busy, execute the conversation command after handling the message
        $conversation = new Conversation(
            $message->getFrom()->getId(),
            $message->getChat()->getId()
        );

        //Fetch conversation command if it exists and execute it
        if ($conversation->exists() && ($command = $conversation->getCommand())) {
            return $this->telegram->executeCommand($command);
        }

        TelegramLog::debug("TEXT: {$message->getText()}");

        switch ($message->getText()) {
            case PublicMain::BUTTON_CONTACT:
                return $this->telegram->executeCommand('contact');
                break;
            case PublicMain::BUTTON_INFO:
                return $this->telegram->executeCommand('info');
                break;
            case PublicMain::BUTTON_ORDER:
                return $this->telegram->executeCommand('order');
                break;
            case PublicMain::BUTTON_REGISTER:
                return $this->telegram->executeCommand('register');
                break;
            case PublicMain::BUTTON_ACCOUNT:
                return $this->telegram->executeCommand('account');
                break;
            default:
                return $this->telegram->executeCommand('start');
        }
    }
}