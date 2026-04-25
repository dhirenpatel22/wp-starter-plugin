<?php
/**
 * Registers and runs all actions and filters.
 *
 * @package WPStarterPlugin\Includes
 */

namespace WPStarterPlugin\Includes;

defined( 'ABSPATH' ) || exit;

class Loader {

	/** @var array<int, array{hook: string, component: object, callback: string, priority: int, args: int}> */
	private array $actions = [];

	/** @var array<int, array{hook: string, component: object, callback: string, priority: int, args: int}> */
	private array $filters = [];

	/**
	 * Queue an action hook.
	 *
	 * @param string $hook
	 * @param object $component
	 * @param string $callback
	 * @param int    $priority
	 * @param int    $accepted_args
	 */
	public function add_action(
		string $hook,
		object $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->actions[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	/**
	 * Queue a filter hook.
	 *
	 * @param string $hook
	 * @param object $component
	 * @param string $callback
	 * @param int    $priority
	 * @param int    $accepted_args
	 */
	public function add_filter(
		string $hook,
		object $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->filters[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	/** Register all queued hooks with WordPress. */
	public function run(): void {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				[ $hook['component'], $hook['callback'] ],
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				[ $hook['component'], $hook['callback'] ],
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
