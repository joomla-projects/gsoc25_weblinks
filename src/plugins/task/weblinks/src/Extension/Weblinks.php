<?php

namespace Joomla\Plugin\Task\Weblinks\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Date\Date;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseAwareInterface;

/**
 * Unpublish task scheduler for com_weblinks.
 *
 * @since  __DEPLOY_VERSION__
 */
final class Weblinks extends CMSPlugin implements SubscriberInterface, DatabaseAwareInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    /**
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const TASKS_MAP = [
        'weblinks.unpublish' => [
            'langConstPrefix' => 'PLG_TASK_WEBLINKS_UNPUBLISH',
            'method'          => 'unpublish',
        ],
    ];

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList'    => 'advertiseRoutines',
            'onExecuteTask'        => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    /**
     * @param   ExecuteTaskEvent  $event  The execute task event
     *
     * @return  integer
     *
     * @since  __DEPLOY_VERSION__
     */
    public function unpublish(ExecuteTaskEvent $event): int
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__weblinks'))
            ->set($db->quoteName('state') . ' = 0')
            ->where($db->quoteName('state') . ' = 1')
            ->where($db->quoteName('publish_down') . ' < ' . $db->quote((new Date())->toSql()))
            ->where($db->quoteName('publish_down') . ' <> ' . $db->quote($db->getNullDate()));

        try {
            $db->setQuery($query)->execute();
        } catch (\Exception $e) {
            $this->logTask($e->getMessage(), 'error');
            return Status::KNOCKOUT;
        }

        return Status::OK;
    }
}
