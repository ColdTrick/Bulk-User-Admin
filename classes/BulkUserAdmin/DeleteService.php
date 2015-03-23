<?php
namespace BulkUserAdmin;

class DeleteService {
	private $queue;
	private $entities;

	private function __construct(\Elgg\Queue\Queue $queue, \Elgg\Database\EntityTable $entities) {
		$this->queue = $queue;
		$this->entities = $entities;
	}

	public function process($end_ts) {
		_elgg_services()->access->setIgnoreAccess(true);
		access_show_hidden_entities(true);
		
		while (time() < $end_ts) {
			$user = $this->queue->dequeue();

			// end of queue
			if ($user === null) {
				return;
			}

			// bad data
			if (!$user instanceof \ElggUser) {
				throw new \UnexpectedValueException("Unexpected entity of type {$user->type}. Expected ElggUser");
			}

			if (!$user->bulk_user_admin_delete_queued) {
				throw new \UnexpectedValueException("User incorrectly scheduled for deletion. Not deleting.");
			}
			
			$user->delete();
		}
	}

	public function enqueue(\ElggUser $user) {
		if (!$user instanceof \ElggUser) {
			throw new \UnexpectedValueException("DeleteService->enqueue() expects an ElggUser object");
		}
		$user->bulk_user_admin_delete_queued = true;
		return $this->queue->enqueue($user);
	}

	public static function getService() {
		$db = _elgg_services()->db;
		$queue = new \Elgg\Queue\DatabaseQueue('bulk_user_admin', $db);

		$entities = _elgg_services()->entityTable;

		return new self($queue, $entities);
	}
}